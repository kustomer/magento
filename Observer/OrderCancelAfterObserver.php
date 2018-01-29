<?php

use Kustomer\KustomerIntegration\Observer\KustomerEventObserver;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class OrderCancelAfterObserver
 */
class OrderCancelAfterObserver extends KustomerEventObserver
{
    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /**
         * @var string $eventName
         * @var \Magento\Sales\Model\Order $order
         * @var \Magento\Customer\Model\Customer $customer
         * @var \Magento\Store\Model\Store $store
         */
        $eventName = $observer->getEventName();
        $order = $observer->getEvent()->getOrder();
        $customer = $order->getCustomer();
        $store = $customer->getStore();

        $data = [
          'order' => $order
        ];

        $this->publish($eventName, $customer, $data, $store);
    }
}