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

use BaconQrCode\Renderer\Image\Svg;
use BaconQrCode\Writer;
use DateTime;
use DateTimeZone;
use Exception;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Header;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\TwoFactorAuth\Model\ResourceModel\Trusted\Collection;
use Mageplaza\TwoFactorAuth\Model\TrustedFactory;
use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Os;
use Sinergi\BrowserDetector\UserAgent;

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
     * @var Header
     */
    protected $header;

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
     * @param Header $header
     * @param TrustedFactory $trustedFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezone,
        TrustedFactory $trustedFactory
    ) {
        $this->_localeDate     = $timezone;
        $this->_trustedFactory = $trustedFactory;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param $userId
     *
     * @return Collection
     */
    public function getTrustedCollection($userId)
    {
        /** @var Collection $trustedCollection */
        $trustedCollection = $this->_trustedFactory->create()->getCollection();
        $trustedCollection->addFieldToFilter('user_id', $userId);

        return $trustedCollection;
    }

    /**
     * @param $date
     *
     * @return DateTime
     * @throws Exception
     */
    public function convertTimeZone($date)
    {
        $dateTime = new DateTime($date, new DateTimeZone('UTC'));
        $dateTime->setTimezone(new DateTimeZone($this->_localeDate->getConfigTimezone()));

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
        $whitelistIp = $this->getConfigGeneral(self::XML_PATH_WHITELIST_IP, $scopeId);

        return explode(',', $whitelistIp);
    }

    /**
     * @param $secret
     *
     * @return string
     */
    public function generateUri($secret)
    {
        $renderer = new Svg();
        $renderer->setHeight(171);
        $renderer->setWidth(171);
        $renderer->setMargin(0);
        $writer = new Writer($renderer);

        return $writer->writeString($secret);
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
                [$low, $high] = explode('-', $range, 2);
            }
            $low   = str_replace('*', '0', $low);
            $high  = str_replace('*', '255', $high);
            $range = $low . '-' . $high;
        }
        if (strpos($range, '-') !== false) {
            [$low, $high] = explode('-', $range, 2);

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
                return ($op === -1);
            }
            if ($ip1Arr[$i] > $ip2Arr[$i]) {
                return ($op === 1);
            }
        }

        return ($op === 0);
    }

    /**
     * @return string
     */
    public function getDeviceName()
    {
        $userAgent = new UserAgent(
            $this->getObject(Header::class)->getHttpUserAgent()
        );
        $os        = new Os($userAgent);
        $browser   = new Browser($userAgent);

        return implode('-', [$os->getName(), $browser->getName(), $browser->getVersion()]);
    }
}
