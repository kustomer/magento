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
         * @var Store $store
         * @var Customer $customer
         */
        $order = $observer->getEvent()->getOrder();
        $eventName = $observer->getEventName();

        if (empty($order))
        {
            return;
        }

        $store = $order->getStore();
        $customer = $order->getCustomer();

        if (!$this->__eventPublisher->isKustomerIntegrationEnabled($store))
        {
            return;
        }

        $data = [
            'order' => $order
        ];

        $this->publish($eventName, $customer, $data, $store);
    }
}