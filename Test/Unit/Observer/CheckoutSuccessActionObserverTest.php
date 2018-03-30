<?php

namespace Kustomer\KustomerIntegration\Test\Unit\Observer;

use Kustomer\KustomerIntegration\Observer\CheckoutSuccessActionObserver;
use Kustomer\KustomerIntegration\Model\EventFactory;
use Kustomer\KustomerIntegration\Helper\Data;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckoutSuccessActionObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CheckoutSuccessActionObserver
     */
    protected $model;

    /**
     * @var EventFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventFactory;

    /**
     * @var Data |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $kustomerDataHelper;

    /**
     * @var OrderRepositoryInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepositoryInterface;

    protected function setUp()
    {
        $this->kustomerDataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventFactory = $this->getMockBuilder(EventFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRepositoryInterface = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new CheckoutSuccessActionObserver(
            $this->kustomerDataHelper,
            $this->eventFactory,
            $this->orderRepositoryInterface
        );
    }

    /**
     * @param int $order_id
     * @dataProvider dataProviderCheckoutEmptyOrderReturn
     */
    public function testCheckoutEmptyOrderReturn(
        $order_id
    )
    {
        $event = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getData',
                'getName',
            ])
            ->getMock();

        $event->expects($this->once())
            ->method('getData')
            ->willReturn([ 'order_ids' => [$order_id] ]);

        $event->expects($this->never())
            ->method('getName');

        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getEvent',
            ])
            ->getMock();

        $observer->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);

        $this->orderRepositoryInterface->expects($this->any())
            ->method('get')
            ->willReturn(null);

        $this->model->execute($observer);
    }

    public function dataProviderCheckoutEmptyOrderReturn()
    {
        return [
            [
                'order_id' => 1
            ]
        ];
    }
}
