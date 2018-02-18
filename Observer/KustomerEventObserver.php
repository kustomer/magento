<?php

namespace Kustomer\KustomerIntegration\Observer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
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
        $headers = array(
            'Authorization' => 'Bearer '.$authToken,
            'User-Agent' => $this->__helperData::USER_AGENT.$this->__helperData::VERSION,
            'Accept' => $this->__helperData::ACCEPT_HEADER,
            'Content-Type' => $this->__helperData::CONTENT_TYPE,
        );

        $this->logger->debug($uri.'\n\t'.implode('\n\t', $headers).'\n'.$body);

        $this->__curl->setHeaders($headers);
        try {
            $this->__curl->post($uri, $body);
        } catch (\Exception $e) {
            $this->logger->error('Failed to connect to Kustomer API', $e);
        }

        $statusCode = $this->__curl->getStatus();

        /**
         * @todo Some kind of retry logic or error logging
         */
        if ($statusCode >= 400)
        {
            $this->logger->error('Failed to connect to Kustomer API');
            return false;
        }

        return true;
    }

    /**
     * @var Data
     */
    protected $__helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $__storeRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $__customerRepository;

    /**
     * @var Curl
     */
    protected $__curl;

    protected $logger;

    /**
     * @param string|int $id
     * @return CustomerInterface|null
     */
    protected function __getCustomerById($id)
    {
        $customer = null;
        try {
            $customer = $this->__customerRepository->getById($id);
        } catch (LocalizedException $e) {
            $this->logger->error('no customer exists by id '.$id, $e);
        }
        return $customer;
    }

    /**
     * @param string $eventName ,
     * @param string $dataType ,
     * @param mixed[] $data
     * @param CustomerInterface $customer
     * @param int|Store|null $store
     */
    protected function __publish($eventName, $dataType, $data, $customer, $store = null)
    {
        $uri = $this->__helperData->getUriByCustomer($customer);

        if (is_int($store) || is_string($store) || empty($store))
        {
            $store_id = $customer->getStoreId();
            $store = $this->__storeRepository->getStore($store_id);
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

        $this->__request($uri, $body, $store);
    }

    /**
     * KustomerEventObserver constructor.
     * @param Data $kustomerDataHelper
     */
    public function __construct(
        Data $kustomerDataHelper
    )
    {
        $this->__helperData = $kustomerDataHelper;
        $this->__customerRepository = $this->__helperData->customerRepository;
        $this->__storeRepository = $this->__helperData->storeManagerInterface;
        $this->__curl = $this->__helperData->curl;
        $this->logger = $this->__helperData->logger;
    }

    /**
     * @param string $dataType - The type of data you are submitting (i.e. "order")
     * @param array $data - The array of data to send to customer
     * @param Customer|CustomerInterface|int|string $customer - The customer object from Magento
     * @param Store|int|null $store - The store ID or store object the event was emitted from
     * @param string $eventName - The name of the event being emitted
     */
    public function publish($dataType, $data, $customer, $store = null, $eventName = null)
    {
        if (!$this->__helperData->isKustomerIntegrationEnabled($eventName, $store))
        {
            $this->logger->debug('kustomer: event '.$eventName.' disabled for scope. not publishing...');
            return;
        }

        $this->logger->debug('kustomer: processing event '.$eventName.'...');
        if (is_string($customer) || is_int($customer))
        {
            $customer = $this->__getCustomerById($customer);
        }

        if ($customer instanceof Customer)
        {
            $customer = $this->__getCustomerById($customer->getId());
        }

        if (!$customer instanceof CustomerInterface)
        {
            $this->logger->error('no customer provided for event '.$eventName);
            return;
        }

        if ($dataType === 'customer' && empty($data))
        {
            $data = $this->__helperData->normalizeCustomer($customer);
        }

        $result = $this->__publish($eventName, $dataType, $data, $customer, $store);
        $this->logger->debug('kustomer: processing for event '.$eventName.' complete. success: '.$result);
    }
}