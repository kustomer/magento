<?php

namespace Kustomer\KustomerIntegration\Helper;

use GuzzleHttp\Client;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;

/**
 * @param AbstractModel $obj
 * @return array
 */
function toPlainArray($obj)
{
    return $obj->getData();
}

class EventPublisher extends AbstractHelper
{
    const XML_PATH_EVENT = 'kustomer/integration/';
    const XML_PATH_ENABLED = 'kustomer/integration/enabled';
    const XML_PATH_API_KEY = 'kustomer/integration/api-key';
    const API_ENDPOINT = 'events';
    const ACCEPT_HEADER = 'application/json';
    const BASE_KUSTOMER_URI = 'https://api.kustomerapp.com/v1/magento/customers/';
    const CONTENT_TYPE = 'application/json';
    const PUBLISH_METHOD = 'POST';
    const USER_AGENT = 'kustomer-magento-extension/';
    const VERSION = '0.0.1';

    /**
     * @param string $uri
     * @param string|null $body
     * @param Store|null $store
     * @return array
     */
    protected function __request($uri, $body = null, $store = null)
    {
        $authToken = $this->__getKustomerApiKey($store);

        $client = new Client([
            'base_uri' => self::BASE_KUSTOMER_URI
        ]);

        $method = self::PUBLISH_METHOD;
        $res = $client->request($method, $uri, [
            'headers' => [
                'Authorization' => 'Bearer '.$authToken,
                'User-Agent' => self::USER_AGENT.self::VERSION,
                'Accept' => self::ACCEPT_HEADER,
                'Content-Type' => self::CONTENT_TYPE,
            ],
            'body' => $body
        ]);

        $statusCode = $res->getStatusCode();

        /**
         * @todo Some kind of retry logic or error logging
         */
        if ($statusCode >= 500)
        {
            return [];
        }

        return json_decode($res->getBody());
    }

    /**
     * @param Store|null $store
     * @return string
     */
    protected function __getKustomerApiKey($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_KEY, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function __getRawData($data)
    {
        return array_map('toPlainArray', $data);
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
     * @param string $eventName,
     * @param Customer $customer
     * @param array $data
     * @param int|Store|null $store
     */
    public function publish($eventName, $customer, $data = [], $store = null)
    {
        $uri = $this->__getUri($customer);
        $arrayData = $this->__getRawData($data);

        if (is_int($store) || is_null($store))
        {
            $store = $customer->getStore();
        }

        $body = json_encode([
            'event' => $eventName,
            'store' => $store->getData(),
            'customer' => $customer->getData(),
            'data' => $arrayData
        ]);

        if (!$this->isKustomerIntegrationEnabled($eventName, $store))
        {
            return;
        }

        $this->__request($uri, $body, $store);
    }
}