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
        $storeId = $customer->getStoreId();

        if (empty($customer))
        {
            return;
        }

        // If customer save handler is enabled do not publish duplicate data
        if ($this->__helperData->isKustomerIntegrationEnabled('customer_save_after_data_object', $storeId))
        {
            return;
        }

        $dataType = 'customer';
        $this->publish($dataType, [], $customer, $storeId, $eventName);
    }
}