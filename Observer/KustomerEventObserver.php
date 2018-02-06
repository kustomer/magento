<?php

namespace Kustomer\KustomerIntegration\Observer;

use Kustomer\KustomerIntegration\Helper\EventPublisher;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\Store;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class KustomerEventObserver
 * @package Kustomer\KustomerIntegration\Observer
 */
abstract class KustomerEventObserver implements ObserverInterface
{
    /**
     * @var EventPublisher|null
     */
    protected $__eventPublisher = null;

    /**
     * KustomerEventObserver constructor.
     * @param EventPublisher $kustomerEventPublisher
     */
    public function __construct(EventPublisher $kustomerEventPublisher)
    {
        $this->__eventPublisher = $kustomerEventPublisher;
    }

    /**
     * @param string $eventName - The name of the event being emitted
     * @param string $dataType - The type of data you are submitting (i.e. "order")
     * @param Customer $customer - The customer object from Magento
     * @param array $data - The array of data to send to customer
     * @param Store|int|null $store - The store ID or store object the event was emitted from
     */
    public function publish($eventName, $dataType = 'customer', $customer, $data = [], $store = null)
    {
        if ($dataType === 'customer' && count($data) === 0)
        {
            $data = $customer;
        }

        $this->__eventPublisher->publish($eventName, $dataType, $customer, $data, $store);
    }
}