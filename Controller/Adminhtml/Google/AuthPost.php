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

namespace Mageplaza\TwoFactorAuth\Controller\Adminhtml\Google;

use PHPGangsta\GoogleAuthenticator;
use Magento\Framework\Session\SessionManager;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Security\Model\AdminSessionsManager;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;

/**
 * Class AuthPost
 * @package Mageplaza\TwoFactorAuth\Controller\Adminhtml\Google
 */
class AuthPost extends Action
{
    /**
     * @var \PHPGangsta\GoogleAuthenticator
     */
    protected $_googleAuthenticator;

    /**
     * @var SessionManager
     */
    protected $_storageSession;

    /**
     * @var AdminSessionsManager
     */
    protected $sessionsManager;

    /**
     * AuthPost constructor.
     *
     * @param Context $context
     * @param GoogleAuthenticator $googleAuthenticator
     * @param SessionManager $storageSession
     * @param AdminSessionsManager $sessionsManager
     */
    public function __construct(
        Context $context,
        GoogleAuthenticator $googleAuthenticator,
        SessionManager $storageSession,
        AdminSessionsManager $sessionsManager
    )
    {
        $this->_googleAuthenticator = $googleAuthenticator;
        $this->_storageSession      = $storageSession;
        $this->sessionsManager      = $sessionsManager;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $authCode   = $this->_request->getParam('auth-code');
        $user       = $this->_storageSession->getData('user');
        $secretCode = $user->getMpTfaSecret();
        try {
            $checkResult = $this->_googleAuthenticator->verifyCode($secretCode, $authCode, 1);
            if ($checkResult) {
                $this->_storageSession->setData(HelperData::MP_GOOGLE_AUTH, true);

                /** perform login */
                $this->_auth->getAuthStorage()->setUser($user);
                $this->_auth->getAuthStorage()->processLogin();

                $this->_eventManager->dispatch(
                    'backend_auth_user_login_success',
                    ['user' => $user]
                );

                /** security auth */
                $this->sessionsManager->processLogin();
                if ($this->sessionsManager->getCurrentSession()->isOtherSessionsTerminated()) {
                    $this->messageManager->addWarning(__('All other open sessions for this account were terminated.'));
                }

                return $this->_getRedirect($this->_backendUrl->getStartupPageUrl());
            } else {
                $this->_storageSession->setData(HelperData::MP_GOOGLE_AUTH, false);
                $this->messageManager->addError(__('Invalid key.'));

                return $this->_getRedirect('mptwofactorauth/google/authindex');
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());

            return $this->_getRedirect('mptwofactorauth/google/authindex');
        }
    }

    /**
     * Get redirect response
     *
     * @param string $path
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    private function _getRedirect($path)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($path);

        return $resultRedirect;
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
