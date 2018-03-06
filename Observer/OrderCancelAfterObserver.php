<?php

namespace Kustomer\KustomerIntegration\Observer;

use Kustomer\KustomerIntegration\Helper\Data;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class OrderCancelAfterObserver
 */
class OrderCancelAfterObserver extends KustomerEventObserver
{

    /**
     * Tells the publisher to check event is enabled in store config before attempting to publish
     * @var bool
     */
    protected $isBuiltIn = true;

    /**
     * @var OrderRepositoryInterface
     */
    protected $__orderRepository;

    /**
     * OrderCancelAfterObserver constructor.
     * @param Data $kustomerDataHelper
     * @param OrderRepositoryInterface $orderRepository
     */
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
         * @var string $eventName
         * @var \Magento\Sales\Model\Order $orderModel
         */
        $eventName = $observer->getEvent()->getName();
        $orderModel = $observer->getEvent()->getData()['order'];
        $order = $this->__orderRepository->get($orderModel->getId());
        $customer = $order->getCustomerId();
        $store = $order->getStoreId();

        $orderData = $this->__helperData->normalizeOrder($order);
        $dataType = 'order';
        $this->publish($dataType, $orderData,  $customer, $store, $eventName);
    }
}