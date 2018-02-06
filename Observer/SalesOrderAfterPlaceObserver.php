<?php

namespace Kustomer\KustomerIntegration\Observer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\Order;
use Magento\Store\Model\Store;

class SalesOrderAfterPlaceObserver extends KustomerEventObserver
{
    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /**
         * @var Order $order
         * @var Customer $customer
         * @var Store $store
         */
        $order = $observer->getEvent()->getOrder();
        $eventName = $observer->getEventName();
        $customer = $order->getCustomer();
        $store = $order->getStore();

        if (empty($order))
        {
            return;
        }


        if (!$this->__eventPublisher->isKustomerIntegrationEnabled($store))
        {
            return;
        }

        $objectType = 'order';
        $data = $order;
        $this->publish($eventName, $objectType, $customer, $data, $store);
    }
}