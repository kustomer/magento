<?php

namespace Kustomer\KustomerIntegration\Helper;

use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;


class Data extends AbstractHelper
{
    const XML_PATH_EVENT = 'kustomer/event/';
    const XML_PATH_ENABLED = 'kustomer/integration/active';
    const XML_PATH_API_KEY = 'kustomer/integration/api_key';
    const API_ENDPOINT = 'events';
    const ACCEPT_HEADER = 'application/json';
    const BASE_KUSTOMER_URI = 'https://api.kustomerapp.com/v1/magento/customers/';
    const CONTENT_TYPE = 'application/json';
    const PUBLISH_METHOD = 'POST';
    const USER_AGENT = 'kustomer-magento-extension/';
    const VERSION = '0.0.1';

    /**
     * @param Customer $customer
     * @return array
     */
    public function normalizeCustomer($customer)
    {
        $model = $customer->getDataModel();

        return array(
            'id' => $customer->getId(),
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
            'address' => $customer->getDefaultBillingAddress(),
            'created_at' => $customer->getCreatedAtTimestamp(),
            'custom_attributes' => $model->getCustomAttributes(),
            'extension_attributes' => $model->getExtensionAttributes(),
            'dob' => $model->getDob(),
        );
    }

    /**
     * @param Order $order
     * @return array
     */
    public function normalizeOrder($order)
    {
        return array(
            'id' => $order->getId(),
            'items' => $order->getAllItems(),
            'state' => $order->getState(),
            'status' => $order->getStatus(),
            'shipping_method' => $order->getShippingMethod(),
            'currency_code' => $order->getOrderCurrencyCode(),
            'subtotal' => $order->getSubtotal(),
            'total_due' => $order->getTotalDue(),
            'total_discount' => $order->getDiscountAmount(),
            'total_paid' => $order->getTotalPaid(),
            'total_refunded' => $order->getTotalRefunded(),
            'custom_attributes' => $order->getCustomAttributes(),
            'extension_attributes' => $order->getExtensionAttributes()
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
     * @param AbstractModel $obj
     * @return array
     */
    public function toPlainArray($obj)
    {
        return $obj->toArray();
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
     * @param Customer $customer
     * @return string
     */
    protected function __getUri($customer)
    {
        $customerId = $customer->getId();
        return $customerId.'/'.self::API_ENDPOINT;
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