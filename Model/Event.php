<?php

namespace Kustomer\KustomerIntegration\Model;

use Magento\Framework\Exception\LocalizedException;

class Event extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Kustomer\KustomerIntegration\Model\ResourceModel\Event
     */
    protected $_resource;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Kustomer\KustomerIntegration\Model\ResourceModel\Event $resource = null,
        \Kustomer\KustomerIntegration\Model\ResourceModel\Event\Collection $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
    }

    protected function _construct()
    {
        $this->_init(\Kustomer\KustomerIntegration\Model\ResourceModel\Event::class);
    }

    /**
     * @return $this
     */
    public function clean()
    {
        $this->_logger->debug('kustomer:event:clean:start', $this->getData());
        $collection = null;
        try {
            $collection = $this->getResourceCollection();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
        }

        if ($collection === null) {
            $this->_logger->debug('kustomer:event:clean:abort no collection found');
            return $this;
        }

        $collection
            ->addFieldToFilter(
                ['is_sent', 'send_count'],
                [
                    ['eq' => 1],
                    ['gteq' => $this->getResource()->getMaxSendCount()]
                ]
            );
        $items = $collection->getItems();
        $deleted = 0;

        /**
         * @var Event $data
         */
        foreach ($items as $data)
        {
            $this->_logger->debug('kustomer:event:clean:item id: '.$data->getId(), $data->getData());
            $this->getResource()->clean($data);
            if ($data->isDeleted())
            {
                $deleted += 1;
            }
        }

        $this->_logger->debug('kustomer:event:clean:done', ['deleted_count' => $deleted]);
        return $this;
    }

    /**
     * @param string $uri
     * @param string $body
     * @param int $store_id
     * @return $this
     */
    public function create(string $uri, string $body, int $store_id)
    {
        $this->setData([
            'uri' => $uri,
            'store_id' => $store_id,
            'body' => $body
        ]);
        $this->save();
        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        $this->_logger->debug('kustomer:event:delete. id: '.$this->getData('event_id'));

        try {
            $this->getResource()->delete($this);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        return $this;
    }

    public function send()
    {
        $this->_logger->debug('kustomer:event:send:start', $this->getData());
        $collection = null;

        try {
            $collection = $this->getResourceCollection();
        } catch (LocalizedException $e) {
            $this->_logger->critical($e);
        }

        if ($collection === null) {
            $this->_logger->debug('kustomer:event:send:abort: no collection found');
            return $this;
        }

        $collection
            ->addFieldToFilter(
                'is_sent',
                ['eq' => 0]
            )->addFieldToFilter(
                'send_count',
                ['lteq' => $this->getResource()->getMaxSendCount()]
            );
        $items = $collection->getItems();
        $sent = 0;

        /**
         * @var Event $data
         */
        foreach ($items as $data)
        {
            $this->_logger->debug('kustomer:event:send:item id: '.$data->getId());

            $this->getResource()->send($data);
            if ($this->getData('is_sent'))
            {
                $sent += 1;
            }
        }

        $this->_logger->debug('kustomer:event:send:done', ['send_count' => $sent]);
        return $this;
    }

    /**
     * @return $this
     */
    public function save()
    {
        $this->_logger->debug('kustomer:event:save id: '.$this->getId());
        $resource = $this->getResource();

        try {
            $resource->save($this);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        return $this;
    }
}