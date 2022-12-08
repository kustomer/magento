<?php

namespace Kustomer\KustomerIntegration\Observer;

use Kustomer\KustomerIntegration\Helper\Data;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class OrderSaveAfterObserver
 */
class OrderSaveAfterObserver extends KustomerEventObserver
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
     * OrderSaveAfterObserver constructor.
     * @param Data $kustomerDataHelper
     * @param \Kustomer\KustomerIntegration\Model\EventFactory $eventFactory
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Data $kustomerDataHelper,
        \Kustomer\KustomerIntegration\Model\EventFactory $eventFactory,
        OrderRepositoryInterface $orderRepository
    )
    {
        parent::__construct($kustomerDataHelper, $eventFactory);
        $this->__orderRepository = $orderRepository;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        try
        {
            /**
             * @var string $eventName
             * @var \Magento\Sales\Model\Order $orderModel
             */
            $eventName = $observer->getEvent()->getName();
            $order = $observer->getEvent()->getData()['order'];
            $customer = $order->getCustomerId();
            $store = $order->getStoreId();
            $orderData = $this->__helperData->normalizeOrder($order);
            $dataType = 'order';
            $this->publish($dataType, $orderData,  $customer, $store, $eventName);
        }
        catch (\Error $e)
        {
            $this->logger->error('OrderSaveAfterObserver ' . $e->getMessage());
        }
    }
}
