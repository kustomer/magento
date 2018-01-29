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
     * @param string $eventName
     * @param Customer $customer
     * @param array $data
     * @param Store|int|null $store
     */
    public function publish($eventName, $customer, $data = [], $store = null)
    {
        $this->__eventPublisher->publish($eventName, $customer, $store, $data);
    }
}