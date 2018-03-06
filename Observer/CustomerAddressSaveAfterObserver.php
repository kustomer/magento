<?php

namespace Kustomer\KustomerIntegration\Observer;

class CustomerAddressSaveAfterObserver extends KustomerEventObserver
{
    protected $isBuiltIn = true;

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $customerAddress \Magento\Customer\Model\Address */
        $customerAddress = $observer->getEvent()->getData()['data_object'];
        $customerId = $customerAddress->getCustomerId();

        try {
            $customer = $this->__helperData->customerRepository->getById($customerId);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
        }

        // this requires some extra work since the new address is saved but not committed to the db
        $addresses = $customer->getAddresses();
        array_push($addresses, $customerAddress);
        $data = $this->__helperData->normalizeCustomer($customer);
        $data['addresses'] = $this->__helperData->normalizeAddresses($addresses);

        $this->publish('customer', [], $customer, $customer->getStoreId(), 'customer_save_after_data_object');
    }
}
