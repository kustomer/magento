<?php

use Kustomer\KustomerIntegration\Observer\KustomerEventObserver;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;

/**
 * Class CustomerAddressSaveAfterObserver
 */
class CustomerAddressSaveAfterObserver extends KustomerEventObserver
{
    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /**
         * @var Customer $customer
         */
        $eventName = $observer->getEvent()->getName();
        $customerAddress = $observer->getCustomerAddress();
        $customer = $customerAddress->getCustomer();

        $dataType = 'customer';
        $this->publish($dataType, [], $customer, null, $eventName);
    }
}