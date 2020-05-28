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

use Exception;
use Google\Authenticator\GoogleAuthenticator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Session\SessionManager;
use Magento\Security\Model\AdminSessionsManager;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;
use Mageplaza\TwoFactorAuth\Model\TrustedFactory;
use Psr\Cache\InvalidArgumentException;

/**
 * Class AuthPost
 * @package Mageplaza\TwoFactorAuth\Controller\Adminhtml\Google
 */
class AuthPost extends Action
{
    /**
     * @var GoogleAuthenticator
     */
    protected $_googleAuthenticator;

    /**
     * @var SessionManager
     */
    protected $_storageSession;

    /**
     * @var AdminSessionsManager
     */
    protected $_sessionsManager;

    /**
     * @var RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var TrustedFactory
     */
    protected $_trustedFactory;

    /**
     * AuthPost constructor.
     *
     * @param Context $context
     * @param GoogleAuthenticator $googleAuthenticator
     * @param SessionManager $storageSession
     * @param AdminSessionsManager $sessionsManager
     * @param RemoteAddress $remoteAddress
     * @param TrustedFactory $trustedFactory
     */
    public function __construct(
        Context $context,
        GoogleAuthenticator $googleAuthenticator,
        SessionManager $storageSession,
        AdminSessionsManager $sessionsManager,
        RemoteAddress $remoteAddress,
        TrustedFactory $trustedFactory
    ) {
        $this->_googleAuthenticator = $googleAuthenticator;
        $this->_storageSession      = $storageSession;
        $this->_sessionsManager     = $sessionsManager;
        $this->_remoteAddress       = $remoteAddress;
        $this->_trustedFactory      = $trustedFactory;

        parent::__construct($context);
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     * @throws InvalidArgumentException
     */
    public function execute()
    {
        $params    = $this->_request->getParams();
        $authCode  = $params['auth-code'];
        $isTrusted = isset($params['trust-device']) ? true : false;

        if ($user = $this->_storageSession->getData('user')) {
            $secretCode = $user->getMpTfaSecret();
            try {
                $checkResult = $this->_googleAuthenticator->checkCode($secretCode, $authCode);
                if ($checkResult) {
                    $this->_storageSession->setData(HelperData::MP_GOOGLE_AUTH, true);

                    /** perform login */
                    $this->_auth->getAuthStorage()->setUser($user);
                    $this->_auth->getAuthStorage()->processLogin();

                    $this->_eventManager->dispatch(
                        'mageplaza_tfa_backend_auth_user_login_success',
                        ['user' => $user, 'mp_is_trusted' => $isTrusted]
                    );

                    /** security auth */
                    $this->_sessionsManager->processLogin();
                    if ($this->_sessionsManager->getCurrentSession()->isOtherSessionsTerminated()) {
                        $this->messageManager->addWarning(__(
                            'All other open sessions for this account were terminated.'
                        ));
                    }

                    return $this->_getRedirect($this->_backendUrl->getStartupPageUrl());
                } else {
                    $this->_storageSession->setData(HelperData::MP_GOOGLE_AUTH, false);
                    $this->messageManager->addError(__('Invalid key.'));

                    return $this->_getRedirect('mptwofactorauth/google/authindex');
                }
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->_getRedirect('mptwofactorauth/google/authindex');
            }
        }

        return $this->_getRedirect('mptwofactorauth/google/authindex');
    }

    /**
     * Get redirect response
     *
     * @param string $path
     *
     * @return Redirect
     */
    private function _getRedirect($path)
    {
        /** @var Redirect $resultRedirect */
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
