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

namespace Mageplaza\TwoFactorAuth\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Trusted
 * @package Mageplaza\TwoFactorAuth\Model\ResourceModel
 */
class Trusted extends AbstractDb
{
    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * Trusted constructor.
     *
     * @param Context $context
     * @param DateTime $dateTime
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        $connectionName = null
    ) {
        $this->_dateTime = $dateTime;

        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_twofactorauth_trusted', 'trusted_id');
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (!$object->getCreatedAt()) {
            $object->setCreatedAt($this->_dateTime->date());
        }

        return $this;
    }

    /**
     * @param $userId
     * @param $deviceName
     * @param $deviceIp
     *
     * @return string
     * @throws LocalizedException
     */
    public function getExistTrusted($userId, $deviceName, $deviceIp)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'trusted_id')
            ->where('user_id = :user_id')
            ->where('name = :name')
            ->where('device_ip = :device_ip');
        $binds   = ['user_id' => (int) $userId, 'name' => $deviceName, 'device_ip' => $deviceIp];

        return $adapter->fetchOne($select, $binds);
    }
}
