<?php

namespace Kustomer\KustomerIntegration\Observer;

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
         * @var \Magento\Sales\Api\Data\OrderInterface $order
         */
        $eventName = $observer->getEvent()->getName();
        $order = $observer->getEvent()->getOrder();
        $customer = $order->getCustomerId();
        $store = $order->getStoreId();

        $orderData = $this->__helperData->normalizeOrder($order);
        $dataType = 'order';
        $this->publish($dataType, $orderData,  $customer, $store, $eventName);
    }
}