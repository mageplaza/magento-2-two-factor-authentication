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

namespace Mageplaza\TwoFactorAuth\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\TwoFactorAuth\Model\TrustedFactory;

/**
 * Class Data
 * @package Mageplaza\TwoFactorAuth\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mptwofactorauth';
    const MP_GOOGLE_AUTH = 'mp_google_auth';

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var TrustedFactory
     */
    protected $_trustedFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $timezone
     * @param TrustedFactory $trustedFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezone,
        TrustedFactory $trustedFactory
    )
    {
        $this->_localeDate = $timezone;
        $this->_trustedFactory = $trustedFactory;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param $userId
     *
     * @return \Mageplaza\TwoFactorAuth\Model\ResourceModel\Trusted\Collection
     */
    public function getTrustedCollection($userId)
    {
        /** @var \Mageplaza\TwoFactorAuth\Model\ResourceModel\Trusted\Collection $trustedCollection */
        $trustedCollection = $this->_trustedFactory->create()->getCollection();
        $trustedCollection->addFieldToFilter('user_id', $userId);

        return $trustedCollection;
    }

    /**
     * @param $date
     *
     * @return \DateTime
     * @throws \Exception
     */
    public function convertTimeZone($date)
    {
        $dateTime = new \DateTime($date, new \DateTimeZone('UTC'));
        $dateTime->setTimezone(new \DateTimeZone($this->_localeDate->getConfigTimezone()));

        return $dateTime;
    }

    /**
     * @param null $scopeId
     * @return mixed
     */
    public function getForceTfaConfig($scopeId = null)
    {
        return $this->getConfigGeneral('force_2fa', $scopeId);
    }
}