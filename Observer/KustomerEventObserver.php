<?php

namespace Kustomer\KustomerIntegration\Observer;

use GuzzleHttp\Client;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\Store;
use Magento\Framework\Event\ObserverInterface;
use Kustomer\KustomerIntegration\Helper\Data;

/**
 * Class KustomerEventObserver
 * @package Kustomer\KustomerIntegration\Observer
 */
abstract class KustomerEventObserver implements ObserverInterface
{

    /**
     * @param string $uri
     * @param string|null $body
     * @param Store|null $store
     * @return boolean
     */
    protected function __request($uri, $body = null, $store = null)
    {
        $authToken = $this->__helperData->getKustomerApiKey($store);

        $client = new Client([
            'base_uri' => $this->__helperData::BASE_KUSTOMER_URI
        ]);

        $method = $this->__helperData::PUBLISH_METHOD;
        $res = $client->request($method, $uri, [
            'headers' => [
                'Authorization' => 'Bearer '.$authToken,
                'User-Agent' => $this->__helperData::USER_AGENT.$this->__helperData::VERSION,
                'Accept' => $this->__helperData::ACCEPT_HEADER,
                'Content-Type' => $this->__helperData::CONTENT_TYPE,
            ],
            'body' => $body
        ]);

        $statusCode = $res->getStatusCode();

        /**
         * @todo Some kind of retry logic or error logging
         */
        if ($statusCode >= 400)
        {
            return false;
        }

        return true;
    }

    /**
     * @var Data|null
     */
    protected $__helperData = null;

    /**
     * @param string $eventName ,
     * @param string $dataType ,
     * @param mixed[] $data
     * @param Customer|int $customer
     * @param int|Store|null $store
     */
    protected function __publish($eventName, $dataType, $data, $customer, $store = null)
    {
        if (is_int($customer))
        {
            $customer = $customer->getById();
        }
        $uri = $this->__getUri($customer);

        if (is_int($store) || is_null($store))
        {
            $store = $customer->getStore();
        }

        $body = json_encode([
            'event' => $eventName,
            'store' => $this->__helperData->normalizeStore($store),
            'customer' => $this->__helperData->normalizeCustomer($customer),
            'data' => [
                'type' => $dataType,
                'data' => $data
            ]
        ]);

        if (!$this->__helperData->isKustomerIntegrationEnabled($eventName, $store))
        {
            return;
        }

        $this->__request($uri, $body, $store);
    }

    /**
     * KustomerEventObserver constructor.
     * @param Data $kustomerDataHelper
     */
    public function __construct(Data $kustomerDataHelper)
    {
        $this->__helperData = $kustomerDataHelper;
    }

    /**
     * @param string $dataType - The type of data you are submitting (i.e. "order")
     * @param array $data - The array of data to send to customer
     * @param Customer $customer - The customer object from Magento
     * @param Store|int|null $store - The store ID or store object the event was emitted from
     * @param string $eventName - The name of the event being emitted
     */
    public function publish($dataType, $data, $customer, $store = null, $eventName = null)
    {
        if ($dataType === 'customer' && empty($data))
        {
            $data = $customer->toArray();
        }

        $this->__publish($eventName, $dataType, $data, $customer, $store);
    }
}