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

        $installer->endSetup();
    }
}
