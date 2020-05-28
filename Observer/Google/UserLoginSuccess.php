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
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\TwoFactorAuth\Helper\Data;
use Mageplaza\TwoFactorAuth\Model\TrustedFactory;

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
     * @var Request
     */
    protected $request;

    /**
     * UserLoginSuccess constructor.
     *
     * @param DateTime $dateTime
     * @param ManagerInterface $messageManager
     * @param TrustedFactory $trustedFactory
     * @param Request $request
     * @param Data $helper
     */
    public function __construct(
        DateTime $dateTime,
        ManagerInterface $messageManager,
        TrustedFactory $trustedFactory,
        Request $request,
        Data $helper
    ) {
        $this->_dateTime       = $dateTime;
        $this->_messageManager = $messageManager;
        $this->_trustedFactory = $trustedFactory;
        $this->helper          = $helper;
        $this->request         = $request;
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
                $ipLogin = explode(',', $this->request->getClientIp());
                if (count($ipLogin) > 1) {
                    if (($key = array_search('127.0.0.1', $ipLogin)) !== false) {
                        unset($ipLogin[$key]);
                    }
                }
                $ipLogin = implode(',', $ipLogin);

                $trusted->setDeviceIp($ipLogin)
                    ->setLastLogin($this->_dateTime->date())
                    ->setName($this->helper->getDeviceName())
                    ->setUserId($user->getId())
                    ->save();
            } catch (Exception $e) {
                $this->_messageManager->addError($e->getMessage());
            }
        }
    }
}
