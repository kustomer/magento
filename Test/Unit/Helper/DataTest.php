<?php

namespace Kustomer\KustomerIntegration\Test\Unit\Data;

use Kustomer\KustomerIntegration\Helper\Data;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Data
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Helper\Context | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    /**
     * @var \Magento\Quote\Model\QuoteRepository | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $curl;

    /**
     * @var \Psr\Log\LoggerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerInterface = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Model\QuoteRepository::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'get'
            ])
            ->getMock();

        $this->pricingHelper = $this->getMockBuilder(\Magento\Framework\Pricing\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->curl = $this->getMockBuilder(\Magento\Framework\HTTP\Client\Curl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Data(
            $this->context,
            $this->storeManagerInterface,
            $this->customerRepository,
            $this->quoteRepository,
            $this->pricingHelper,
            $this->curl,
            $this->logger
        );
    }

    /**
     * @param mixed[] $fixture
     * @param mixed[] $item
     * @param mixed[] $shipping
     * @param mixed[] $payment
     * @param mixed[] $billing
     * @dataProvider dataProviderNormalizeOrderReturnArray
     */
    public function testNormalizeOrderReturnArray(
        $fixture,
        $item,
        $shipping,
        $payment,
        $billing
    )
    {
        $itemMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn($item['name']);

        $itemMock
            ->expects($this->once())
            ->method('getSku')
            ->willReturn($item['sku']);

        $itemMock
            ->expects($this->once())
            ->method('getProductId')
            ->willReturn($item['product_id']);

        $itemMock
            ->expects($this->once())
            ->method('getQtyOrdered')
            ->willReturn($item['quantity']);

        $itemMock
            ->expects($this->once())
            ->method('getPrice')
            ->willReturn($item['price']);

        $itemMock
            ->expects($this->once())
            ->method('getDiscountAmount')
            ->willReturn($item['discount']);

        $itemMock
            ->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($item['total']);

        $region = $this->getMockBuilder(\Magento\Customer\Api\Data\RegionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $region
            ->expects($this->atLeastOnce())
            ->method('getRegion')
            ->willReturn('Washington');

        $billingAddress = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderAddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $billingAddress
            ->expects($this->once())
            ->method('getStreet')
            ->willReturn($billing['street']);

        $billingAddress
            ->expects($this->once())
            ->method('getRegion')
            ->willReturn($region);

        $billingAddress
            ->expects($this->once())
            ->method('getCity')
            ->willReturn($billing['city']);

        $billingAddress
            ->expects($this->once())
            ->method('getPostcode')
            ->willReturn($billing['zip']);

        $billingAddress
            ->expects($this->once())
            ->method('getCountryId')
            ->willReturn($billing['country']);

        $orderItems = [$itemMock];
        $order = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order
            ->expects($this->once())
            ->method('getEntityId')
            ->willReturn($fixture['id']);

        $order
            ->expects($this->once())
            ->method('getQuoteId')
            ->willReturn(4);

        $order
            ->expects($this->once())
            ->method('getItems')
            ->willReturn($orderItems);

        $order
            ->expects($this->once())
            ->method('getState')
            ->willReturn($fixture['state']);

        $order
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn($fixture['status']);

        $order
            ->expects($this->once())
            ->method('getOrderCurrencyCode')
            ->willReturn($fixture['currency_code']);

        $order
            ->expects($this->once())
            ->method('getSubtotal')
            ->willReturn(4.9900);

        $order
            ->expects($this->once())
            ->method('getTotalDue')
            ->willReturn(6.3200);

        $order
            ->expects($this->once())
            ->method('getDiscountAmount')
            ->willReturn(0.0000);

        $order
            ->expects($this->once())
            ->method('getTotalPaid')
            ->willReturn(0.0000);

        $order
            ->expects($this->once())
            ->method('getTotalRefunded')
            ->willReturn(0.0000);

        $order
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn([]);

        $order
            ->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);

        $shippingAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getShippingDescription',
                'getShippingMethod',
                'getShippingAmount',
                'getStreet',
                'getRegion',
                'getCity',
                'getPostcode',
                'getCountryId'
            ])
            ->getMock();

        $shippingAddress
            ->expects($this->once())
            ->method('getShippingDescription')
            ->willReturn($shipping['description']);

        $shippingAddress
            ->expects($this->once())
            ->method('getShippingMethod')
            ->willReturn($shipping['method']);

        $shippingAddress
            ->expects($this->once())
            ->method('getShippingAmount')
            ->willReturn($shipping['amount']);

        $shippingAddress
            ->expects($this->once())
            ->method('getStreet')
            ->willReturn($shipping['street']);

        $shippingAddress
            ->expects($this->once())
            ->method('getRegion')
            ->willReturn($region);

        $shippingAddress
            ->expects($this->once())
            ->method('getCity')
            ->willReturn($shipping['city']);

        $shippingAddress
            ->expects($this->once())
            ->method('getPostcode')
            ->willReturn($shipping['zip']);

        $shippingAddress
            ->expects($this->once())
            ->method('getCountryId')
            ->willReturn($shipping['country']);

        $paymentMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getMethod',
                'getCcLast4',
                'getPoNumber',
                'getUpdatedAt',
                'getCreatedAt'
            ])
            ->getMock();

        $paymentMock
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn($payment['method']);

        $paymentMock
            ->expects($this->once())
            ->method('getCcLast4')
            ->willReturn($payment['cc_last4']);

        $paymentMock
            ->expects($this->once())
            ->method('getPoNumber')
            ->willReturn($payment['po_number']);

        $paymentMock
            ->expects($this->once())
            ->method('getUpdatedAt')
            ->willReturn($payment['updated_at']);

        $paymentMock
            ->expects($this->once())
            ->method('getCreatedAt')
            ->willReturn($payment['created_at']);

        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getShippingAddress',
                'getPayment'
            ])
            ->getMock();

        $quote
            ->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($shippingAddress);

        $quote
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $this->quoteRepository
            ->expects($this->once())
            ->method('get')
            ->willReturn($quote);

        $this->pricingHelper
            ->method('currency')
            ->will($this->returnCallback(function (float $value) { return '$'.number_format((float)$value, 2, '.', ''); }));

        $result = $this->model->normalizeOrder($order);
        $this->assertEquals($fixture, $result);
    }

    public function dataProviderNormalizeOrderReturnArray()
    {
        $now = time();
        return [
            [
                'fixture' => [
                    'id' => 3,
                    'items' => [
                        [
                            'name' => 'foo',
                            'sku' => 'foobar123',
                            'product_id' => 44,
                            'quantity' => 1,
                            'price' => '$4.99',
                            'discount' => '$0.00',
                            'total' => '$4.99'
                        ]
                    ],
                    'currency_code' => 'USD',
                    'subtotal' => '$4.99',
                    'total_due' => '$6.32',
                    'total_discount' => '$0.00',
                    'total_paid' => '$0.00',
                    'total_refunded' => '$0.00',
                    'extension_attributes' => [],
                    'state' => 'new',
                    'status' => 'pending',
                    'shipping_description' => 'UPS Next Day Air',
                    'shipping_method' => 'ups_1D',
                    'shipping_amount' => '$1.33',
                    'shipping_street' => '5808 Lake Washington Blvd Suite 700',
                    'shipping_state' => 'Washington',
                    'shipping_city' => 'Kirkland',
                    'shipping_zip' => '98008',
                    'shipping_country' => 'US',
                    'payment_method' => 'worldpay',
                    'payment_cc_last4' => 4522,
                    'payment_po_number' => null,
                    'payment_updated_at' => $now,
                    'payment_created_at' => $now,
                    'billing_state' => 'Washington',
                    'billing_city' => 'Seattle',
                    'billing_zip' => '98012',
                    'billing_country' => 'US',
                    'billing_street' => '1401 W Bertona Ave Apt 5',
                ],
                'item' => [
                    'name' => 'foo',
                    'sku' => 'foobar123',
                    'product_id' => 44,
                    'quantity' => 1,
                    'price' => 4.9900,
                    'discount' => 0.0000,
                    'total' => 4.9900
                ],
                'shipping' => [
                    'description' => 'UPS Next Day Air',
                    'method' => 'ups_1D',
                    'amount' => 1.3300,
                    'region' => 'Washington',
                    'city' => 'Kirkland',
                    'zip' => '98008',
                    'country' => 'US',
                    'street' => ['5808 Lake Washington Blvd', 'Suite 700'],
                ],
                'payment' => [
                    'method' => 'worldpay',
                    'cc_last4' => 4522,
                    'po_number' => null,
                    'updated_at' => $now,
                    'created_at' => $now
                ],
                'billing' => [
                    'region' => 'Washington',
                    'city' => 'Seattle',
                    'zip' => '98012',
                    'country' => 'US',
                    'street' => ['1401 W Bertona Ave', 'Apt 5'],
                ],
            ]
        ];
    }
}