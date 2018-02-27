<?php

namespace Kustomer\KustomerIntegration\Model\ResourceModel\Event;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Kustomer\KustomerIntegration\Model\Event::class, \Kustomer\KustomerIntegration\Model\ResourceModel\Event::class);
    }
}
