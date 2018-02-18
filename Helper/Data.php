<?php

namespace Kustomer\KustomerIntegration\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const XML_PATH_EVENT = 'kustomer/event/';
    const XML_PATH_ENABLED = 'kustomer/integration/active';
    const XML_PATH_API_KEY = 'kustomer/integration/api_key';
    const API_ENDPOINT = 'events';
    const ACCEPT_HEADER = 'application/json';
    const KUSTOMER_DOMAIN = 'https://api.kustomerapp.com';
    const BASE_KUSTOMER_URI = '/v1/magento/customers/';
    const CONTENT_TYPE = 'application/json';
    const PUBLISH_METHOD = 'POST';
    const USER_AGENT = 'kustomer-magento-extension/';
    const VERSION = '0.0.1';

    public $customerRepository;
    public $storeManagerInterface;
    public $curl;
    public $logger;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManagerInterface,
        CustomerRepositoryInterface $customerRepository,
        Curl $curl,
        LoggerInterface $logger
    )
    {
        parent::__construct($context);
        $this->storeManagerInterface = $storeManagerInterface;
        $this->customerRepository = $customerRepository;
        $this->curl = $curl;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getKustomerUri()
    {
        $domain = getenv('KUSTOMER_API_DOMAIN');
        if (empty($domain))
        {
            return self::KUSTOMER_DOMAIN;
        }
        return $domain;
    }

    /**
     * @param CustomerInterface $customer
     * @return array
     */
    public function normalizeCustomer($customer)
    {

        return array(
            'id' => $customer->getId(),
            'name' => $customer->getFirstname().' '.$customer->getLastname(),
            'email' => $customer->getEmail(),
            'address' => $customer->getDefaultBilling(),
            'created_at' => $customer->getCreatedAt(),
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
        return array(
            'id' => $order->getEntityId(),
            'items' => $this->normalizeOrderItems($order->getItems()),
            'state' => $order->getState(),
            'status' => $order->getStatus(),
            'currency_code' => $order->getOrderCurrencyCode(),
            'shipping_amount' => $order->getShippingAmount(),
            'subtotal' => $order->getSubtotal(),
            'total_due' => $order->getTotalDue(),
            'total_discount' => $order->getDiscountAmount(),
            'total_paid' => $order->getTotalPaid(),
            'total_refunded' => $order->getTotalRefunded(),
            'extension_attributes' => $order->getExtensionAttributes()
        );
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
                'quantity' => $item->getQtyOrdered(),
                'price' => $item->getPrice(),
                'discount' => $item->getDiscountAmount(),
                'total' => $item->getRowTotal()
            ));
        }
        return $normalized;
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
     * @param CustomerInterface $customer
     * @return string
     */
    public function getUriByCustomer($customer)
    {
        $customerId = $customer->getId();
        $baseUri = $this->getKustomerUri().self::BASE_KUSTOMER_URI;
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
}