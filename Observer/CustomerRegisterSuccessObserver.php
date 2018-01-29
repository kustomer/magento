<?php

namespace Kustomer\KustomerIntegration\Observer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Store\Model\Store;

class CustomerRegisterSuccessObserver extends KustomerEventObserver
{
    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /**
         * @var Store $store
         * @var Customer $customer
         */
        $customer = $observer->getEvent()->getCustomer();
        $eventName = $observer->getEventName();

        if (empty($customer))
        {
            return;
        }

        $store = $customer->getStore();
        $this->publish($eventName, $customer, $store);
    }
}