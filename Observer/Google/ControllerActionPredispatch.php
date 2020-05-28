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

use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManager;
use Magento\User\Model\User;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;

/**
 * Class ControllerActionPredispatch
 * @package Mageplaza\TwoFactorAuth\Observer\Backend
 */
class ControllerActionPredispatch implements ObserverInterface
{
    /**
     * Backend url interface
     *
     * @var UrlInterface
     */
    protected $url;

    /**
     * Backend authorization session
     *
     * @var AuthSession
     */
    protected $authSession;

    /**
     * Action flag
     *
     * @var ActionFlag
     */
    protected $actionFlag;

    /**
     * @var SessionManager
     */
    protected $_storageSession;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * AuthObserver constructor.
     *
     * @param UrlInterface $url
     * @param AuthSession $authSession
     * @param ActionFlag $actionFlag
     * @param SessionManager $storageSession
     * @param ManagerInterface $messageManager
     * @param HelperData $helperData
     */
    public function __construct(
        UrlInterface $url,
        AuthSession $authSession,
        ActionFlag $actionFlag,
        SessionManager $storageSession,
        ManagerInterface $messageManager,
        HelperData $helperData
    ) {
        $this->url             = $url;
        $this->authSession     = $authSession;
        $this->actionFlag      = $actionFlag;
        $this->_storageSession = $storageSession;
        $this->_messageManager = $messageManager;
        $this->_helperData     = $helperData;
    }

    /**
     * Get current user
     * @return User|null
     */
    private function getUser()
    {
        return $this->authSession->getUser();
    }

    /**
     * Force admin to change password
     *
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return;
        }

        $user                    = $this->getUser();
        $allowForce2faActionList = [
            'adminhtml_system_account_index',
            'adminhtml_system_account_save',
            'adminhtml_auth_logout',
            'mptwofactorauth_google_register',
            'mptwofactorauth_auto_save',
            'mui_index_render'
        ];
        /** @var Action $controller */
        $controller = $observer->getEvent()->getControllerAction();
        /** @var RequestInterface $request */
        $request = $observer->getEvent()->getRequest();

        if ($user
            && $this->_helperData->getForceTfaConfig()
            && !$user->getMpTfaStatus()
            && !in_array($request->getFullActionName(), $allowForce2faActionList)
        ) {
            $this->_messageManager->addError(__('Forced 2FA is enabled, so please register the 2FA authentication.'));
            $controller->getResponse()->setRedirect($this->url->getUrl('adminhtml/system_account/'));
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $this->actionFlag->set('', Action::FLAG_NO_POST_DISPATCH, true);
        }
    }
}
