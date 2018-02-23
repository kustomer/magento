<?php

namespace Kustomer\KustomerIntegration\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        /**
         * Create table 'kustomer_event'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('kustomer_event')
        )->addColumn(
            'event_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Event ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Store ID'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'last_sent_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null, ['nullable' => true, 'default' => null],
            'Time of last attempted send'
        )->addColumn(
            'send_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['default' => '0'],
            'The number of times the event has failed to send'
        )->addColumn(
            'body',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            409600,
            [],
            'Request body'
        )->addColumn(
            'uri',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Destination URI of event'
        )->addColumn(
            'error_message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Error message of latest request, if any'
        )->addColumn(
            'is_sent',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'event was sent successfully'
        )->addIndex(
            $installer->getIdxName('kustomer_event_is_sent_last_sent_at', ['is_sent', 'send_count']),
            ['is_sent', 'send_count']
        )->addIndex(
            $installer->getIdxName('kustomer_event_last_sent_at_send_count_is_sent', ['last_sent_at', 'send_count', 'is_sent']),
            ['is_sent', 'send_count']
        )->addForeignKey(
            $installer->getFkName('kustomer_event', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        )->setComment(
            'Kustomer Event Table'
        );
        $installer->getConnection()->createTable($table);
    }
}