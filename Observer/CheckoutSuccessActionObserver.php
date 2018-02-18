<?php

namespace Kustomer\KustomerIntegration\Observer;

use Kustomer\KustomerIntegration\Helper\Data;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\Store;
use Magento\Sales\Api\OrderRepositoryInterface;

class CheckoutSuccessActionObserver extends KustomerEventObserver
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $__orderRepository;

    public function __construct(
        Data $kustomerDataHelper,
        OrderRepositoryInterface $orderRepository
    )
    {
        parent::__construct($kustomerDataHelper);
        $this->__orderRepository = $orderRepository;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /**
         * @var OrderInterface $order
         * @var Customer $customer
         * @var Store $store
         */
        $orderId = $observer->getEvent()->getData()['order_ids'][0];
        $order = $this->__orderRepository->get($orderId);
        $eventName = $observer->getEvent()->getName();
        $customer = $order->getCustomerId();
        $store = $order->getStoreId();

        if (empty($order))
        {
            $this->logger->warning('kustomer: no order found with id '.$orderId.'. skipping.');
            return;
        }

        $objectType = 'order';
        $data = $this->__helperData->normalizeOrder($order);
        $this->publish($objectType, $data, $customer, $store, $eventName);
    }
}