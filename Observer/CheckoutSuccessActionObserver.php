<?php

namespace Kustomer\KustomerIntegration\Observer;

use Kustomer\KustomerIntegration\Helper\Data;
use Kustomer\KustomerIntegration\Model\EventFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Api\OrderRepositoryInterface;

class CheckoutSuccessActionObserver extends KustomerEventObserver
{
    /**
     * Tells the publisher to check event is enabled in store config before attempting to publish
     * @var bool
     */
    protected $isBuiltIn = true;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $__orderRepository;

    /**
     * CheckoutSuccessActionObserver constructor.
     * @param Data $kustomerDataHelper
     * @param EventFactory $eventFactory
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Data $kustomerDataHelper,
        EventFactory $eventFactory,
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
        /**
         * @var \Magento\Sales\Api\Data\OrderInterface $order
         * @var \Magento\Customer\Model\Customer $customer
         * @var \Magento\Store\Model\Store $store
         */
        $order = $observer->getEvent()->getData()['order'];
        $eventName = $observer->getEvent()->getName();
        $customer = $order->getCustomerId();
        $store = $order->getStoreId();
        $objectType = 'order';
        $data = $this->__helperData->normalizeOrder($order);
        $this->publish($objectType, $data, $customer, $store, $eventName);
    }
}