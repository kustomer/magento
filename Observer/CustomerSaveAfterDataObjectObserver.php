<?php

namespace Kustomer\KustomerIntegration\Observer;

use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class CustomerAddressSaveAfterObserver
 */
class CustomerSaveAfterDataObjectObserver extends KustomerEventObserver
{
    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /**
         * @var \Magento\Customer\Model\Customer $customer
         */
        $eventName = $observer->getEvent()->getName();
        $customer = $observer->getEvent()->getData()['customer_data_object'];

        $dataType = 'customer';
        $this->publish($dataType, [], $customer, null, $eventName);
    }
}