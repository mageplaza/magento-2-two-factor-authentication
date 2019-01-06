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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Framework\Session\SessionManager;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;

/**
 * Class AuthObserver
 * @package Mageplaza\TwoFactorAuth\Observer\Backend
 */
class AuthObserver implements ObserverInterface
{
    /**
     * Backend url interface
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $url;

    /**
     * Backend authorization session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * Action flag
     *
     * @var \Magento\Framework\App\ActionFlag
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
    )
    {
        $this->url             = $url;
        $this->authSession     = $authSession;
        $this->actionFlag      = $actionFlag;
        $this->_storageSession = $storageSession;
        $this->_messageManager = $messageManager;
        $this->_helperData     = $helperData;
    }

    /**
     * Get current user
     * @return \Magento\User\Model\User|null
     */
    private function getUser()
    {
        return $this->authSession->getUser();
    }

    /**
     * Force admin to change password
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return;
        }

        $user       = $this->getUser();
        $actionList = [
            'mptwofactorauth_google_index',
            'mptwofactorauth_google_auth',
            'adminhtml_auth_forgotpassword',
            'adminhtml_auth_logout'
        ];

        $force2faActionList = [
            'adminhtml_user_edit',
            'adminhtml_auth_logout'
        ];
        /** @var \Magento\Framework\App\Action\Action $controller */
        $controller = $observer->getEvent()->getControllerAction();
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getEvent()->getRequest();
        if ($user
            && $this->_helperData->getConfigGeneral('force_2fa')
            && !$user->getMpTfaStatus()
            && !in_array($request->getFullActionName(), $force2faActionList)) {
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $url = $this->url->getUrl('adminhtml/user/edit', ['user_id' => $user->getId()]);
            $this->_messageManager->addError(__('Force 2FA is enabled, please must register the 2FA authentication.'));
            $controller->getResponse()->setRedirect($url);
        }
        if ($user
            && !in_array($request->getFullActionName(), $actionList)
            && $user->getMpTfaStatus()
            && !$this->_storageSession->getData(HelperData::MP_GOOGLE_AUTH)) {
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $url = $this->url->getUrl('mptwofactorauth/google/index');
            $controller->getResponse()->setRedirect($url);
        }
    }
}
