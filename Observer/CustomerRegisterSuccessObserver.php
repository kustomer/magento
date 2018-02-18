<?php

namespace Kustomer\KustomerIntegration\Observer;

use Magento\Framework\Event\Observer as EventObserver;

class CustomerRegisterSuccessObserver extends KustomerEventObserver
{
    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /**
         * @var \Magento\Customer\Api\Data\CustomerInterface $customer
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