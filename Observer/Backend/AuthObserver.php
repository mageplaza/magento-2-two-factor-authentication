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

namespace Mageplaza\TwoFactorAuth\Observer\Backend;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\User\Model\Backend\Config\ObserverConfig;
use Magento\User\Model\ResourceModel\User as ResourceUser;
use Magento\User\Model\User;
use Magento\Framework\Event\ObserverInterface;
use Magento\User\Model\UserFactory;

/**
 * Class AuthObserver
 * @package Mageplaza\TwoFactorAuth\Observer\Backend
 */
class AuthObserver implements ObserverInterface
{
    /**
     * Backend configuration interface
     *
     * @var ObserverConfig
     */
    protected $observerConfig;

    /**
     * Admin user resource model
     *
     * @var ResourceUser
     */
    protected $userResource;

    /**
     * Backend url interface
     *
     * @var UrlInterface
     */
    protected $url;

    /**
     * Backend authorization session
     *
     * @var Session
     */
    protected $authSession;

    /**
     * Factory class for user model
     *
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * Encryption model
     *
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * Message manager interface
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param ObserverConfig $observerConfig
     * @param ResourceUser $userResource
     * @param UrlInterface $url
     * @param Session $authSession
     * @param UserFactory $userFactory
     * @param EncryptorInterface $encryptor
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ObserverConfig $observerConfig,
        ResourceUser $userResource,
        UrlInterface $url,
        Session $authSession,
        UserFactory $userFactory,
        EncryptorInterface $encryptor,
        ManagerInterface $messageManager
    ) {
        $this->observerConfig = $observerConfig;
        $this->userResource = $userResource;
        $this->url = $url;
        $this->authSession = $authSession;
        $this->userFactory = $userFactory;
        $this->encryptor = $encryptor;
        $this->messageManager = $messageManager;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $password = $observer->getEvent()->getPassword();
        /** @var User $user */
        $user = $observer->getEvent()->getUser();
        $authResult = $observer->getEvent()->getResult();

        die('31132');
    }
}
