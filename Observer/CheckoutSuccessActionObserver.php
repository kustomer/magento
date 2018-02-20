<?php

namespace Kustomer\KustomerIntegration\Observer;

use Kustomer\KustomerIntegration\Helper\Data;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Api\OrderRepositoryInterface;

class CheckoutSuccessActionObserver extends KustomerEventObserver
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
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
         * @var \Magento\Sales\Api\Data\OrderInterface $order
         * @var \Magento\Customer\Model\Customer $customer
         * @var \Magento\Store\Model\Store $store
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