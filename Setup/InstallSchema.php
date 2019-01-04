<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_TwoFactorAuth
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\TwoFactorAuth\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Mageplaza\TwoFactorAuth\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        if ($installer->tableExists('admin_user')) {
            $columns = [
                'mp_tfa_enable' => [
                    'type'    => Table::TYPE_INTEGER,
                    'length'  => 1,
                    'comment' => 'Mageplaza TFA Enable',
                ],
                'mp_tfa_qr_url' => [
                    'type'    => Table::TYPE_TEXT,
                    'length'  => 255,
                    'comment' => 'Mageplaza TFA QR Code URL',
                ],
                'mp_tfa_secret' => [
                    'type'    => Table::TYPE_TEXT,
                    'length'  => 255,
                    'comment' => 'Mageplaza TFA Secret Code',
                ]
            ];

            $userTable = $installer->getTable('admin_user');
            foreach ($columns as $name => $definition) {
                $connection->addColumn($userTable, $name, $definition);
            }
        }

        if (!$installer->tableExists('mageplaza_twofactorauth_trusted')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_twofactorauth_trusted'))
                ->addColumn('trusted_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ], 'Trusted ID')
                ->addColumn('user_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'User ID')
                ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable => false'], 'Device Name')
                ->addColumn('device_ip', Table::TYPE_TEXT, '64k', [], 'Device IP')
                ->addColumn('last_login', Table::TYPE_TIMESTAMP, null, [], 'Device Last Login')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Trusted Created At')
                ->addIndex($installer->getIdxName('mageplaza_twofactorauth_trusted', ['trusted_id']), ['trusted_id'])
                ->addIndex($installer->getIdxName('mageplaza_twofactorauth_trusted', ['user_id']), ['user_id'])
                ->addForeignKey(
                    $installer->getFkName('mageplaza_twofactorauth_trusted', 'user_id', 'admin_user', 'user_id'),
                    'user_id',
                    $installer->getTable('admin_user'),
                    'user_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Trusted Device Table');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}