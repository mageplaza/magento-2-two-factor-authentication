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

use Endroid\QrCode\QrCode as EndroidQrCode;
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
    const CONFIG_MODULE_PATH    = 'mptwofactorauth';
    const XML_PATH_FORCE_2FA    = 'force_2fa';
    const XML_PATH_WHITELIST_IP = 'whitelist_ip';
    const MP_GOOGLE_AUTH        = 'mp_google_auth';

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
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Mageplaza\TwoFactorAuth\Model\TrustedFactory $trustedFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezone,
        TrustedFactory $trustedFactory
    )
    {
        $this->_localeDate     = $timezone;
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
     *
     * @return mixed
     */
    public function getForceTfaConfig($scopeId = null)
    {
        return $this->getConfigGeneral(self::XML_PATH_FORCE_2FA, $scopeId);
    }

    /**
     * @param null $scopeId
     *
     * @return mixed
     */
    public function getWhitelistIpsConfig($scopeId = null)
    {
        $whitelistIp  = $this->getConfigGeneral(self::XML_PATH_WHITELIST_IP, $scopeId);
        $whitelistIps = explode(',', $whitelistIp);

        return $whitelistIps;
    }

    /**
     * @param $secret
     *
     * @return string
     * @throws \Endroid\QrCode\Exception\InvalidWriterException
     */
    public function generateUri($secret)
    {
        $qrCode = new EndroidQrCode($secret);
        $qrCode->setSize(400);

        $qrCode->setWriterByName('png');

        return $qrCode->writeDataUri();
    }

    /**
     * Check Ip
     *
     * @param $ip
     * @param $range
     *
     * @return bool
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function checkIp($ip, $range)
    {
        if (strpos($range, '*') !== false) {
            $low = $high = $range;
            if (strpos($range, '-') !== false) {
                list($low, $high) = explode('-', $range, 2);
            }
            $low   = str_replace('*', '0', $low);
            $high  = str_replace('*', '255', $high);
            $range = $low . '-' . $high;
        }
        if (strpos($range, '-') !== false) {
            list($low, $high) = explode('-', $range, 2);

            return $this->ipCompare($ip, $low, 1) && $this->ipcompare($ip, $high, -1);
        }

        return $this->ipCompare($ip, $range);
    }

    /**
     * @param $ip1
     * @param $ip2
     * @param int $op
     *
     * @return bool
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    private function ipCompare($ip1, $ip2, $op = 0)
    {
        $ip1Arr = explode('.', $ip1);
        $ip2Arr = explode('.', $ip2);

        for ($i = 0; $i < 4; $i++) {
            if ($ip1Arr[$i] < $ip2Arr[$i]) {
                return ($op == -1);
            }
            if ($ip1Arr[$i] > $ip2Arr[$i]) {
                return ($op == 1);
            }
        }

        return ($op == 0);
    }
}