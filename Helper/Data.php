<?php

namespace Kustomer\KustomerIntegration\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const XML_PATH_EVENT = 'kustomer/event/';
    const XML_PATH_ENABLED = 'kustomer/integration/active';
    const XML_PATH_API_KEY = 'kustomer/integration/api_key';
    const XML_PATH_ORG_NAME = 'kustomer/integration/org_name';
    const API_ENDPOINT = 'events';
    const ACCEPT_HEADER = 'application/json';
    const KUSTOMER_DOMAIN = 'https://api.kustomerapp.com';
    const BASE_KUSTOMER_URI = '/v1/magento/customers/';
    const CONTENT_TYPE = 'application/json';
    const PUBLISH_METHOD = 'POST';
    const USER_AGENT = 'kustomer-magento-extension/';
    const VERSION = '1.0.0';

    public $customerRepository;
    public $storeManagerInterface;
    public $quoteRepository;
    public $pricingHelper;
    public $logger;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManagerInterface,
        CustomerRepositoryInterface $customerRepository,
        QuoteRepository $quoteRepository,
        PricingHelper $pricingHelper
    )
    {
        parent::__construct($context);
        $this->quoteRepository = $quoteRepository;
        $this->pricingHelper = $pricingHelper;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->customerRepository = $customerRepository;
        $this->logger = $context->getLogger();
    }

    /**
     * @param string $subdomain
     * @return string
     */
    public function getKustomerUri($subdomain)
    {
        $domain = getenv('KUSTOMER_API_DOMAIN');

        if (empty($domain))
        {
            $domain = self::KUSTOMER_DOMAIN;
        }

        $domainParts = explode("://", $domain, 2);

        $protocol = $domainParts[0];
        $host = $domainParts[1];

        return "$protocol://$subdomain.$host";
    }

    /**
 * @param \Magento\Customer\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|QuoteAddress $address
     * @return array
     */
    public function normalizeAddress($address)
    {
        $street = $address->getStreet();

        if (is_array($street)) {
            $street = implode(' ', $street);
        }

        $region = $address->getRegion();

        $n = array(
            'street' => $street,
            'city' => $address->getCity(),
            'zip' => $address->getPostcode(),
            'country' => $address->getCountryId()
        );

        if ($region instanceof RegionInterface) {
            $n['state'] = $region->getRegion();
        } else if (is_string($region)) {
            $n['state'] = $region;
        }

        return $n;
    }

    /**
     * @param \Magento\Customer\Api\Data\AddressInterface[] $addresses
     * @return mixed[]
     */
    public function normalizeAddresses($addresses)
    {
        $normal = [];

        if (empty($addresses)) {
            return $normal;
        }

        foreach ($addresses as $address) {
            $n = $this->normalizeAddress($address);
            array_push($normal, $n);
        }

        return $normal;
    }

    /**
     * @param float $value
     * @return string
     */
    public function normalizeNumericValue($value)
    {
        return round((float)$value, 2);
    }

    /**
     * @param CustomerInterface $customer
     * @return array
     */
    public function normalizeCustomer($customer)
    {
        if (is_array($customer) && $customer['guest']) {
            return $customer;
        }
        return array(
            'id' => $customer->getId(),
            'name' => $customer->getFirstname().' '.$customer->getLastname(),
            'email' => $customer->getEmail(),
            'addresses' => $this->normalizeAddresses($customer->getAddresses()),
            'phones' => $this->normalizePhonesFromCustomer($customer),
            'created_at' => $customer->getCreatedAt(),
            'updated_at' => $customer->getUpdatedAt(),
            'custom_attributes' => $customer->getCustomAttributes(),
            'extension_attributes' => $customer->getExtensionAttributes(),
            'dob' => $customer->getDob(),
        );
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    public function normalizeOrder($order)
    {

        $quote = $this->quoteRepository->get($order->getQuoteId());

        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        $paymentData = $quote->getPayment();
        $orderArray = array(
            'id' => $order->getEntityId(),
            'increment_id' => $order->getIncrementId(),
            'items' => $this->normalizeOrderItems($order->getItems()),
            'state' => $order->getState(),
            'status' => $order->getStatus(),
            'currency_code' => $order->getOrderCurrencyCode(),
            'subtotal' => $this->normalizeNumericValue($order->getSubtotal()),
            'total_due' => $this->normalizeNumericValue($order->getTotalDue()),
            'total_discount' => $this->normalizeNumericValue($order->getDiscountAmount()),
            'total_paid' => $this->normalizeNumericValue($order->getTotalPaid()),
            'total_refunded' => $this->normalizeNumericValue($order->getTotalRefunded()),
            'extension_attributes' => $order->getExtensionAttributes(),
        );

        if (!empty($paymentData)) {
            $payment = $this->normalizePayment($paymentData);
            foreach ($payment as $key => $value) {
                $orderArray['payment_'.$key] = $value;
            }
            unset($key);
            unset($value);
        }

        if($order->getCustomerIsGuest()) {
            $customer_email = $order->getCustomerEmail();
            $customer_name = $order->getCustomerName();

            $orderArray['customer_email'] = $customer_email;
            $orderArray['customer_name'] = $customer_name;
        }

        if (!empty($shippingAddress)) {
            $shipping = $this->normalizeShipping($shippingAddress);
            foreach ($shipping as $key => $value)
            {
                $orderArray['shipping_'.$key] = $value;
            }
            unset($key);
            unset($value);
        }

        if (!empty($billingAddress)) {
            $billing = $this->normalizeAddress($billingAddress);
            foreach ($billing as $key => $value) {
                $orderArray['billing_'.$key] = $value;
            }
            unset($key);
            unset($value);
        }

        return $orderArray;
    }

    /**
     * @param Payment $payment
     * @return array
     */
    public function normalizePayment(Payment $payment)
    {
        return array(
            'method' => $payment->getMethod(),
            'cc_last4' => $payment->getCcLast4(),
            'po_number' => $payment->getPoNumber(),
            'updated_at' => $payment->getUpdatedAt(),
            'created_at' => $payment->getCreatedAt()
        );
    }

    /**
     * @param CustomerInterface $customer
     * @return string[]
     */
    public function normalizePhonesFromCustomer(CustomerInterface $customer)
    {
        $phones = [];
        $addresses = $customer->getAddresses();

        if (empty($addresses)) {
            return $phones;
        }

        foreach ($addresses as $address) {
            $phone = $address->getTelephone();
            if ($phone) {
                array_push($phones, $phone);
            }
        }

        return $phones;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface[] $items
     * @return mixed[]
     */
    public function normalizeOrderItems($items)
    {
        $normalized = array();
        foreach ($items as $item)
        {
            array_push($normalized, array(
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'product_id' => $item->getProductId(),
                'quantity' => $this->normalizeNumericValue($item->getQtyOrdered()),
                'price' => $this->normalizeNumericValue($item->getPrice()),
                'discount' => $this->normalizeNumericValue($item->getDiscountAmount()),
                'total' => $this->normalizeNumericValue($item->getRowTotal()),
            ));
        }
        return $normalized;
    }

    /**
     * @param  QuoteAddress $shipping
     * @return array
     */
    public function normalizeShipping($shipping)
    {
        return array_merge(
            array(
                'description' => $shipping->getShippingDescription(),
                'method' => $shipping->getShippingMethod(),
                'amount' => $this->normalizeNumericValue($shipping->getShippingAmount()),
            ),
            $this->normalizeAddress($shipping)
        );
    }

    /**
     * @param Store $store
     * @return array
     */
    public function normalizeStore($store)
    {
        return array(
            'id' => $store->getId(),
            'name' => $store->getName(),
            'url' => $store->getCurrentUrl()
        );
    }

    /**
     * @param Store|null $store
     * @return string
     */
    public function getKustomerApiKey($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_KEY, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param Store|null $store
     * @return string
     */
    public function getKustomerOrgName($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORG_NAME, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param CustomerInterface $customer
     * @param Store|null $store
     * @return string
     */
    public function getUriByCustomer($customer, $store = null)
    {
        if (is_array($customer) && $customer['guest']) {
            $customerId = 'guest';
        } else {
            $customerId = $customer->getId();
        }
        $subdomain = $this->getKustomerOrgName($store);
        $baseUri = $this->getKustomerUri($subdomain).self::BASE_KUSTOMER_URI;
        return $baseUri.$customerId.'/'.self::API_ENDPOINT;
    }

    /**
     * @param string $eventName
     * @param Store|null $store
     * @return bool
     */
    protected function __isEventEnabled($eventName, $store = null)
    {
        if (empty($eventName))
        {
            return true;
        }
        return $this->scopeConfig->isSetFlag(self::XML_PATH_EVENT.$eventName, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param string $eventName
     * @param Store|null $store
     * @return bool
     */
    public function isKustomerIntegrationEnabled($eventName, $store = null)
    {
        $hasApiKey = boolval($this->scopeConfig->getValue(self::XML_PATH_API_KEY, ScopeInterface::SCOPE_STORE, $store));
        $isKustomerEnabled = $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $store);
        $isEventEnabled = $this->__isEventEnabled($eventName, $store);
        return $hasApiKey && $isKustomerEnabled && $isEventEnabled;
    }

    /**
     * @param string $uri
     * @param string|null $body
     * @param Store|null $store
     * @return array
     */
    public function request($uri, $body = null, $store = null)
    {
        $authToken = $this->getKustomerApiKey($store);
        $headers = array(
            'Authorization: Bearer '.$authToken,
            'User-Agent: '.self::USER_AGENT.self::VERSION,
            'Accept: '.self::ACCEPT_HEADER,
            'Content-Type: '.self::CONTENT_TYPE,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3000);

        // $this->curl->setHeaders($headers);
        $res = curl_exec($ch);
        $e = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $bodyMessage = substr($res, $header_size);

        if ($e)
        {
            return [
                'success' => false,
                'error' => $e,
            ];
        }

        if ($statusCode >= 400)
        {
            return [
                'success' => false,
                'error' => $statusCode.': '.$bodyMessage
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }
}
