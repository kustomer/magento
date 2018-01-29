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
         * @var Address $customerAddress
         * @var Customer $customer
         */
        $eventName = $observer->getEventName();
        $customerAddress = $observer->getCustomerAddress();
        $customer = $customerAddress->getCustomer();
        $store = $customer->getStore();

        $data = [
          'address' => $customerAddress
        ];

        $this->publish($eventName, $customer, $data, $store);
    }
}