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

use Source\UserAgentParser;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Exception\Plugin\AuthenticationException as PluginAuthenticationException;
use Magento\Framework\Event\ManagerInterface;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Auth\StorageInterface;
use Magento\Backend\Model\Auth\Credential\StorageInterface as CredentialStorageInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\ModelFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;

/**
 * Backend Auth model
 */
class Auth extends \Magento\Backend\Model\Auth
{
    /**
     * @var UserAgentParser
     */
    protected $_userAgentParser;

    /**
     * @var RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var \Mageplaza\TwoFactorAuth\Model\TrustedFactory
     */
    protected $_trustedFactory;

    /**
     * Auth constructor.
     *
     * @param ManagerInterface $eventManager
     * @param Data $backendData
     * @param StorageInterface $authStorage
     * @param CredentialStorageInterface $credentialStorage
     * @param ScopeConfigInterface $coreConfig
     * @param ModelFactory $modelFactory
     * @param RemoteAddress $remoteAddress
     * @param UserAgentParser $userAgentParser
     * @param DateTime $dateTime
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
        RemoteAddress $remoteAddress,
        UserAgentParser $userAgentParser,
        DateTime $dateTime,
        HelperData $helperData,
        TrustedFactory $trustedFactory
    )
    {
        $this->_userAgentParser = $userAgentParser;
        $this->_remoteAddress   = $remoteAddress;
        $this->_dateTime = $dateTime;
        $this->_helperData = $helperData;
        $this->_trustedFactory = $trustedFactory;

        parent::__construct($eventManager, $backendData, $authStorage, $credentialStorage, $coreConfig, $modelFactory);
    }

    /**
     * Perform login process
     *
     * @param string $username
     * @param string $password
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
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
                /** @var \Mageplaza\TwoFactorAuth\Model\Trusted $trusted */
                $trusted = $this->_trustedFactory->create();
                $userAgent  = $this->_userAgentParser->parse_user_agent();
                $deviceName = $userAgent['platform'] . '-' . $userAgent['browser'] . '-' . $userAgent['version'];
                $existTrusted = $trusted->getResource()->getExistTrusted(
                    $this->getCredentialStorage()->getId(),
                    $deviceName,
                    $this->_remoteAddress->getRemoteAddress());
                if ($this->_helperData->isEnabled()
                    && $this->getCredentialStorage()->getMpTfaStatus()
                    && !$existTrusted) {
                    $this->_eventManager->dispatch(
                        'mageplaza_tfa_backend_auth_user_before_login_success',
                        ['user' => $this->getCredentialStorage()]
                    );
                } else {
                    $trusted->load($existTrusted)->setLastLogin($this->_dateTime->date())->save();
                    $this->getAuthStorage()->setUser($this->getCredentialStorage());
                    $this->getAuthStorage()->processLogin();

                    $this->_eventManager->dispatch(
                        'backend_auth_user_login_success',
                        ['user' => $this->getCredentialStorage()]
                    );
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
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $username, 'exception' => $e]
            );
            self::throwException(
                __($e->getMessage() ?: 'You did not sign in correctly or your account is temporarily disabled.')
            );
        }
    }
}
