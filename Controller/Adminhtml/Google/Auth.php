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
use Magento\Backend\Model\Auth\Session as AuthSession;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;

/**
 * Class Auth
 * @package Mageplaza\TwoFactorAuth\Controller\Adminhtml\Google
 */
class Auth extends Action
{
    /**
     * Page result factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var AuthSession
     */
    protected $_authSession;

    /**
     * @var \PHPGangsta\GoogleAuthenticator
     */
    protected $_googleAuthenticator;

    /**
     * @var SessionManager
     */
    protected $_storageSession;

    /**
     * Auth constructor.
     *
     * @param Context $context
     * @param AuthSession $authSession
     * @param GoogleAuthenticator $googleAuthenticator
     * @param SessionManager $storageSession
     */
    public function __construct(
        Context $context,
        AuthSession $authSession,
        GoogleAuthenticator $googleAuthenticator,
        SessionManager $storageSession
    )
    {
        $this->_authSession         = $authSession;
        $this->_googleAuthenticator = $googleAuthenticator;
        $this->_storageSession      = $storageSession;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $authCode   = $this->_request->getParam('auth-code');
        $user       = $this->_authSession->getUser();
        $secretCode = $user->getMpTfaSecret();
        try {
            $checkResult = $this->_googleAuthenticator->verifyCode($secretCode, $authCode, 1);
            if ($checkResult) {
                $this->_storageSession->setData(HelperData::MP_GOOGLE_AUTH, true);

                return $this->_getRedirect($this->_backendUrl->getStartupPageUrl());
            } else {
                $this->_storageSession->setData(HelperData::MP_GOOGLE_AUTH, false);
                $this->messageManager->addError(__('Invalid key.'));

                return $this->_getRedirect('mptwofactorauth/google/index');
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());

            return $this->_getRedirect('mptwofactorauth/google/index');
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
}
