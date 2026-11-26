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

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\TwoFactorAuth\Helper\Data;
use Mageplaza\TwoFactorAuth\Model\ClientIp;
use Mageplaza\TwoFactorAuth\Model\TrustedFactory;
use Mageplaza\TwoFactorAuth\Model\TrustedToken;

/**
 * Class UserLoginSuccess
 * @package Mageplaza\TwoFactorAuth\Observer\Google
 */
class UserLoginSuccess implements ObserverInterface
{
    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var TrustedFactory
     */
    protected $_trustedFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ClientIp
     */
    protected $_clientIp;

    /**
     * @var TrustedToken
     */
    protected $_trustedToken;

    /**
     * UserLoginSuccess constructor.
     *
     * @param DateTime $dateTime
     * @param ManagerInterface $messageManager
     * @param TrustedFactory $trustedFactory
     * @param Data $helper
     * @param ClientIp $clientIp
     * @param TrustedToken $trustedToken
     */
    public function __construct(
        DateTime $dateTime,
        ManagerInterface $messageManager,
        TrustedFactory $trustedFactory,
        Data $helper,
        ClientIp $clientIp,
        TrustedToken $trustedToken
    ) {
        $this->_dateTime       = $dateTime;
        $this->_messageManager = $messageManager;
        $this->_trustedFactory = $trustedFactory;
        $this->helper          = $helper;
        $this->_clientIp       = $clientIp;
        $this->_trustedToken   = $trustedToken;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $user      = $observer->getEvent()->getUser();
        $isTrusted = $observer->getEvent()->getMpIsTrusted();
        if ($user && $isTrusted) {
            $trusted = $this->_trustedFactory->create();
            try {
                $ipLogin = $this->_clientIp->getIp();
                $name    = $this->helper->getDeviceName();
                $token   = $this->_trustedToken->generate();
                $trusted->setDeviceIp($ipLogin)
                    ->setLastLogin($this->_dateTime->date())
                    ->setCreatedAt($this->_dateTime->date())
                    ->setName($name)
                    ->setUserId($user->getId())
                    ->setTokenHash($this->_trustedToken->hash($token))
                    ->save();

                $this->_trustedToken->setCookie(
                    $token,
                    (int) $this->helper->getConfigGeneral('trust_time')
                );
            } catch (Exception $e) {
                $this->_messageManager->addErrorMessage($e->getMessage());
            }
        }
    }
}
