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

namespace Mageplaza\TwoFactorAuth\Model\ResourceModel\Trusted;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageplaza\TwoFactorAuth\Model\ResourceModel\Trusted;

/**
 * Class Collection
 * @package Mageplaza\TwoFactorAuth\Model\ResourceModel\Trusted
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'trusted_id';

    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(\Mageplaza\TwoFactorAuth\Model\Trusted::class, Trusted::class);
    }
}
