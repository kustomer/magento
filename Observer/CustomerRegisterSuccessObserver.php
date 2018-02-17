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
        $eventName = $observer->getEvent()->getName();

        if (empty($customer))
        {
            return;
        }

        $dataType = 'customer';
        $this->publish($dataType, [], $customer, null, $eventName);
    }
}