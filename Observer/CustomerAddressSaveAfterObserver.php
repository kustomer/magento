<?php

namespace Kustomer\KustomerIntegration\Observer;

class CustomerAddressSaveAfterObserver extends KustomerEventObserver
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $customerAddress \Magento\Customer\Model\Address */
        $customerAddress = $observer->getEvent()->getData()['data_object'];
        $customerId = $customerAddress->getCustomerId();

        $this->logger->debug('kustomer:event:customer_address_save_after', ['customerId' => $customerId, 'customer_address' => $customerAddress]);

        $this->publish('customer', [], $customerId, null, 'customer_save_after_data_object');
    }
}
