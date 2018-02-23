<?php

namespace Kustomer\KustomerIntegration\Model\ResourceModel;

class Event extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const MAX_SEND_COUNT = 6;
    const EVENT_EXPIRATION_SENT_HOURS = 24;
    const EVENT_EXPIRATION_ERROR_HOURS = 168;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Kustomer\KustomerIntegration\Helper\Data
     */
    protected $dataHelper;

    /**
     * KustomerEvent constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Kustomer\KustomerIntegration\Helper\Data $dataHelper
     * @param string|null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Kustomer\KustomerIntegration\Helper\Data $dataHelper,
        string $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->dataHelper = $dataHelper;
        $this->date = $date;
        $this->dateTime = $dateTime;
    }

    protected function _construct()
    {
        $this->_init('kustomer_event', 'event_id');
    }

    /**
     * @param $hours
     * @return float|int
     */
    protected function hoursToSeconds($hours)
    {
        return $hours * 60 * 60;
    }

    /**
     * @param \Kustomer\KustomerIntegration\Model\Event $event
     * @return bool
     */
    protected function isSentExpired($event)
    {
        $eventData = $event->getData();
        $expiryTime = (int)$this->date->gmtDate('U', $eventData['last_sent_at']) + $this->hoursToSeconds(self::EVENT_EXPIRATION_SENT_HOURS);
        $isTimeExpired = time() > $expiryTime;
        return $eventData['is_sent'] && $isTimeExpired;
    }

    /**
     * @param \Kustomer\KustomerIntegration\Model\Event $event
     * @return bool
     */
    protected function isErrorExpired($event)
    {
        $eventData = $event->getData();
        $expiry = (int)$this->date->gmtDate('U', $eventData['last_sent_at']) + $this->hoursToSeconds(self::EVENT_EXPIRATION_ERROR_HOURS);
        $isTimeExpired = time() > $expiry;
        $isBelowSendLimit = $eventData['send_count'] >= self::MAX_SEND_COUNT;
        return !$eventData['is_sent'] && $isTimeExpired && $isBelowSendLimit;
    }

    /**
     * @param $event
     * @return bool
     */
    protected function isExpired($event)
    {
        return $this->isSentExpired($event) || $this->isErrorExpired($event);
    }

    /**
     * @param \Kustomer\KustomerIntegration\Model\Event $event
     * @return bool
     */
    protected function isSendable(\Kustomer\KustomerIntegration\Model\Event $event)
    {
        $eventData = $event->getData();
        $sendCount = $eventData['send_count'];
        $lastSentAt = $eventData['last_sent_at'];
        $isSent = $eventData['is_sent'];
        $sentStamp = (int)$this->date->gmtDate('U', $lastSentAt);
        $isSendTime = !$lastSentAt || $sentStamp + ($sendCount**2) > time();
        $isBelowLimit = $sendCount < self::MAX_SEND_COUNT;

        return !$isSent && $isSendTime && $isBelowLimit;
    }

    /**
     * @param \Kustomer\KustomerIntegration\Model\Event $event
     * @param string $errorMessage
     * @return \Kustomer\KustomerIntegration\Model\Event
     */
    protected function sendEventError(\Kustomer\KustomerIntegration\Model\Event $event, string $errorMessage)
    {
        $eventData = $event->getData();
        $eventData['last_sent_at'] = $this->date->gmtDate('c', time());
        $eventData['sendCount'] += 1 || 1;
        $eventData['error_message'] = $errorMessage;

        $event->setData($eventData);
        $event->save();
        return $event;
    }

    /**
     * @param \Kustomer\KustomerIntegration\Model\Event $event
     * @return \Kustomer\KustomerIntegration\Model\Event
     */
    protected function sendEventSuccess(\Kustomer\KustomerIntegration\Model\Event $event)
    {
        $eventData = $event->getData();
        $lastSentAt = $this->date->gmtDate('c', time());
        $eventData['last_sent_at'] = $lastSentAt;
        $eventData['is_sent'] = 1;
        $eventData['send_count'] += 1 || 1;
        $event->setData($eventData);
        $event->save();
        return $event;
    }

    /**
     * @param \Kustomer\KustomerIntegration\Model\Event $event
     * @return \Kustomer\KustomerIntegration\Model\Event
     */
    protected function _send(\Kustomer\KustomerIntegration\Model\Event $event)
    {
        $eventData = $event->getData();
        $result = $this->dataHelper->request($eventData['uri'], $event['body'], $event['store_id']);
        if ($result['success'])
        {
            return $this->sendEventSuccess($event);
        }

        return $this->sendEventError($event, $result['error']);
    }

    /**
     * @param \Kustomer\KustomerIntegration\Model\Event $event
     */
    protected function _clean(\Kustomer\KustomerIntegration\Model\Event $event)
    {
        $event->delete();
    }

    /**
     * @param \Kustomer\KustomerIntegration\Model\Event $event
     * @return \Kustomer\KustomerIntegration\Model\Event
     */
    public function send(\Kustomer\KustomerIntegration\Model\Event $event)
    {
        if ($this->isSendable($event))
        {
            $this->_send($event);
        }

        return $event;
    }

    /**
     * @param \Kustomer\KustomerIntegration\Model\Event $event
     * @return \Kustomer\KustomerIntegration\Model\Event
     */
    public function clean(\Kustomer\KustomerIntegration\Model\Event $event)
    {
        if ($this->isExpired($event))
        {
            $this->_clean($event);
        }

        return $event;
    }

    public function getMaxSendCount()
    {
        return self::MAX_SEND_COUNT;
    }
}