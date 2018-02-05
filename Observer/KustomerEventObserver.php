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
     * @param Customer $customer - The customer object from Magento
     * @param array $data - An associated array of objects to be sent to Kustomer. Each key should match the type of object being sent. (i.e. "order" => $order_object)
     * @param Store|int|null $store - The store ID or store object the event was emitted from
     */
    public function publish($eventName, $customer, $data = [], $store = null)
    {
        $this->__eventPublisher->publish($eventName, $customer, $data, $store);
    }
}