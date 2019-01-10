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

namespace Mageplaza\TwoFactorAuth\Observer\Google;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\TwoFactorAuth\Model\TrustedFactory;
use Source\UserAgentParser;

/**
 * Class UserLoginSuccess
 * @package Mageplaza\TwoFactorAuth\Observer\Google
 */
class UserLoginSuccess implements ObserverInterface
{
    /**
     * @var RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var UserAgentParser
     */
    protected $_userAgentParser;

    /**
     * @var TrustedFactory
     */
    protected $_trustedFactory;

    /**
     * UserLoginSuccess constructor.
     *
     * @param RemoteAddress $remoteAddress
     * @param DateTime $dateTime
     * @param ManagerInterface $messageManager
     * @param UserAgentParser $userAgentParser
     * @param TrustedFactory $trustedFactory
     */
    public function __construct(
        RemoteAddress $remoteAddress,
        DateTime $dateTime,
        ManagerInterface $messageManager,
        UserAgentParser $userAgentParser,
        TrustedFactory $trustedFactory
    )
    {
        $this->_remoteAddress   = $remoteAddress;
        $this->_dateTime        = $dateTime;
        $this->_messageManager  = $messageManager;
        $this->_userAgentParser = $userAgentParser;
        $this->_trustedFactory  = $trustedFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $userAgent  = $this->_userAgentParser->parse_user_agent();
        $user       = $observer->getEvent()->getUser();
        $isTrusted  = $observer->getEvent()->getMpIsTrusted();
        $deviceName = $userAgent['platform'] . '-' . $userAgent['browser'] . '-' . $userAgent['version'];
        if ($user && $isTrusted) {
            $trusted = $this->_trustedFactory->create();
            try {
                $trusted->setDeviceIp($this->_remoteAddress->getRemoteAddress())
                    ->setLastLogin($this->_dateTime->date())
                    ->setName($deviceName)
                    ->setUserId($user->getId())->save();
            } catch (\Exception $e) {
                $this->_messageManager->addError($e->getMessage());
            }
        }
    }
}