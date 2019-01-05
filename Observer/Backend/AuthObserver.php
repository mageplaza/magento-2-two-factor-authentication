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

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Backend\Model\UrlInterface;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\Auth\Session as AuthSession;

/**
 * Class AuthObserver
 * @package Mageplaza\TwoFactorAuth\Observer\Backend
 */
class AuthObserver implements ObserverInterface
{
    /**
     * Authorization interface
     *
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * Backend url interface
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $url;

    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

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
     * AuthObserver constructor.
     *
     * @param AuthorizationInterface $authorization
     * @param UrlInterface $url
     * @param Session $session
     * @param AuthSession $authSession
     * @param ActionFlag $actionFlag
     */
    public function __construct(
        AuthorizationInterface $authorization,
        UrlInterface $url,
        Session $session,
        AuthSession $authSession,
        ActionFlag $actionFlag
    )
    {
        $this->authorization = $authorization;
        $this->url           = $url;
        $this->session       = $session;
        $this->authSession   = $authSession;
        $this->actionFlag    = $actionFlag;
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
        $user = $this->getUser();
        $actionList = [
            'mptwofactorauth_google_index'
        ];

        /** @var \Magento\Framework\App\Action\Action $controller */
        $controller = $observer->getEvent()->getControllerAction();
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getEvent()->getRequest();
        if ($user
            && !in_array($request->getFullActionName(), $actionList)
            && $this->authSession->isFirstPageAfterLogin()) {
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $url = $this->url->getUrl('mptwofactorauth/google/index');
            $controller->getResponse()->setRedirect($url);
        }
    }
}
