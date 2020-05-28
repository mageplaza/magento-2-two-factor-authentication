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

namespace Mageplaza\TwoFactorAuth\Model;

use DateTimeZone;
use Exception;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Auth\Credential\StorageInterface as CredentialStorageInterface;
use Magento\Backend\Model\Auth\StorageInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Data\Collection\ModelFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\Plugin\AuthenticationException as PluginAuthenticationException;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;

/**
 * Backend Auth model
 */
class Auth extends \Magento\Backend\Model\Auth
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var DateTime
     */
    protected $_dateTime;

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
     * Action flag
     *
     * @var ActionFlag
     */
    protected $actionFlag;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var TrustedFactory
     */
    protected $_trustedFactory;

    /**
     * @var bool
     */
    protected $_isTrusted = false;

    /**
     * Auth constructor.
     *
     * @param ManagerInterface $eventManager
     * @param Data $backendData
     * @param StorageInterface $authStorage
     * @param CredentialStorageInterface $credentialStorage
     * @param ScopeConfigInterface $coreConfig
     * @param ModelFactory $modelFactory
     * @param Request $request
     * @param DateTime $dateTime
     * @param UrlInterface $url
     * @param ResponseInterface $response
     * @param SessionManager $storageSession
     * @param ActionFlag $actionFlag
     * @param HelperData $helperData
     * @param TrustedFactory $trustedFactory
     */
    public function __construct(
        ManagerInterface $eventManager,
        Data $backendData,
        StorageInterface $authStorage,
        CredentialStorageInterface $credentialStorage,
        ScopeConfigInterface $coreConfig,
        ModelFactory $modelFactory,
        Request $request,
        DateTime $dateTime,
        UrlInterface $url,
        ResponseInterface $response,
        SessionManager $storageSession,
        ActionFlag $actionFlag,
        HelperData $helperData,
        TrustedFactory $trustedFactory
    ) {
        $this->request         = $request;
        $this->_dateTime       = $dateTime;
        $this->_url            = $url;
        $this->_response       = $response;
        $this->_storageSession = $storageSession;
        $this->actionFlag      = $actionFlag;
        $this->_helperData     = $helperData;
        $this->_trustedFactory = $trustedFactory;

        parent::__construct($eventManager, $backendData, $authStorage, $credentialStorage, $coreConfig, $modelFactory);
    }

    /**
     * Perform login process
     *
     * @param string $username
     * @param string $password
     *
     * @throws PluginAuthenticationException
     * @throws Exception
     * @throws AuthenticationException
     */
    public function login($username, $password)
    {
        if (empty($username) || empty($password)) {
            self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
        }

        try {
            $this->_initCredentialStorage();
            $this->getCredentialStorage()->login($username, $password);
            if ($this->getCredentialStorage()->getId()) {
                /** @var Trusted $trusted */
                $trusted   = $this->_trustedFactory->create();
                $ipAddress = explode(',', $this->request->getClientIp());
                if (count($ipAddress) > 1) {
                    if (($key = array_search('127.0.0.1', $ipAddress)) !== false) {
                        unset($ipAddress[$key]);
                    }
                }
                foreach ($ipAddress as $ipLogin) {
                    $existTrusted = $trusted->getResource()
                        ->getExistTrusted(
                            $this->getCredentialStorage()->getId(),
                            $this->_helperData->getDeviceName(),
                            $ipLogin
                        );
                    if ($existTrusted
                        && $this->_helperData->getConfigGeneral('trust_device')) {
                        /** @var AbstractModel $currentDevice */
                        $currentDevice         = $trusted->load($existTrusted);
                        $currentDeviceCreateAt = new \DateTime($currentDevice->getCreatedAt(), new DateTimeZone('UTC'));
                        $currentDateObj        = new \DateTime($this->_dateTime->date(), new DateTimeZone('UTC'));
                        $dateDiff              = date_diff($currentDateObj, $currentDeviceCreateAt);
                        $dateDiff              = $dateDiff->format('%d.%h%i%s');
                        if ($dateDiff > (int) $this->_helperData->getConfigGeneral('trust_time')) {
                            $currentDevice->delete();
                        } else {
                            $currentDevice->setLastLogin($this->_dateTime->date())->save();
                            $this->_isTrusted = true;
                        }
                    }
                    $ipsAddress = $this->_helperData->getWhitelistIpsConfig();
                    foreach ($ipsAddress as $item) {
                        if ($this->_helperData->checkIp($ipLogin, $item)) {
                            $this->_isTrusted = true;
                            break;
                        }
                    }
                    /** verify auth code */
                    if ($this->_helperData->isEnabled()
                        && $this->getCredentialStorage()->getMpTfaStatus()
                        && !$this->_isTrusted) {
                        $user = $this->getCredentialStorage();
                        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                        $this->_storageSession->setData('user', $user);
                        $url = $this->_url->getUrl('mptwofactorauth/google/authindex');
                        $this->_response->setRedirect($url);
                        /** login process  */
                    } else {
                        $this->getAuthStorage()->setUser($this->getCredentialStorage());
                        $this->getAuthStorage()->processLogin();

                        $this->_eventManager->dispatch(
                            'backend_auth_user_login_success',
                            ['user' => $this->getCredentialStorage()]
                        );
                    }
                }
            }

            if (!$this->getAuthStorage()->getUser()) {
                self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
            }
        } catch (PluginAuthenticationException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $username, 'exception' => $e]
            );
            throw $e;
        } catch (LocalizedException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $username, 'exception' => $e]
            );
            self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
        }
    }
}
