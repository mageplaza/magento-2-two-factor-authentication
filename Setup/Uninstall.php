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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Class Uninstall
 * @package Mageplaza\TwoFactorAuth\Setup
 */
class Uninstall implements UninstallInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(Unused)
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->getConnection()->dropColumn($setup->getTable('admin_user'), 'mp_tfa_enable');
        $setup->getConnection()->dropColumn($setup->getTable('admin_user'), 'mp_tfa_qr_url');
        $setup->getConnection()->dropColumn($setup->getTable('admin_user'), 'mp_tfa_secret');
    }
}
