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
        $eventName = $observer->getEvent()->getName();
        $order = $observer->getEvent()->getOrder();
        $customer = $order->getCustomer();
        $store = $customer->getStore();

        $orderData = $this->__helperData->normalizeOrder($order);
        $dataType = 'order';
        $this->publish($dataType, $orderData,  $customer, $store, $eventName);
    }
}