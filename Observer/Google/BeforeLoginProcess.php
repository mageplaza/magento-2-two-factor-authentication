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
use Magento\Framework\UrlInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\App\ResponseInterface;

/**
 * Class BeforeLoginProcess
 * @package Mageplaza\TwoFactorAuth\Observer\Google
 */
class BeforeLoginProcess implements ObserverInterface
{
    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @var SessionManager
     */
    protected $_storageSession;

    /**
     * BeforeLoginProcess constructor.
     *
     * @param UrlInterface $url
     * @param ResponseInterface $response
     * @param SessionManager $storageSession
     */
    public function __construct(
        UrlInterface $url,
        ResponseInterface $response,
        SessionManager $storageSession
    )
    {
        $this->_url = $url;
        $this->_response = $response;
        $this->_storageSession = $storageSession;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $user = $observer->getEvent()->getUser();
        if ($user) {
            $this->_storageSession->setData('user', $user);
            $url = $this->_url->getUrl('mptwofactorauth/google/authindex');
            $this->_response->setRedirect($url);
        }
    }
}