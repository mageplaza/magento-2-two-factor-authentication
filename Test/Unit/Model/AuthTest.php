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

declare(strict_types=1);

namespace Mageplaza\TwoFactorAuth\Test\Unit\Model;

use Magento\Backend\Helper\Data as BackendData;
use Magento\Backend\Model\Auth\Credential\StorageInterface as CredentialStorageInterface;
use Magento\Backend\Model\Auth\StorageInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http as Response;
use Magento\Framework\Data\Collection\ModelFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;
use Mageplaza\TwoFactorAuth\Model\Auth;
use Mageplaza\TwoFactorAuth\Model\ClientIp;
use Mageplaza\TwoFactorAuth\Model\ResourceModel\Trusted as TrustedResource;
use Mageplaza\TwoFactorAuth\Model\TrustedFactory;
use Mageplaza\TwoFactorAuth\Model\TrustedToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AuthTest extends TestCase
{
    /** @var ManagerInterface|MockObject */
    private $eventManager;

    /** @var DataObject */
    private $credential;

    /** @var object */
    private $authStorage;

    /** @var Response|MockObject */
    private $response;

    /** @var ActionFlag|MockObject */
    private $actionFlag;

    /** @var HelperData|MockObject */
    private $helperData;

    /** @var TrustedFactory|MockObject */
    private $trustedFactory;

    /** @var TrustedResource|MockObject */
    private $trustedResource;

    /** @var object */
    private $trustedModel;

    /** @var ClientIp|MockObject */
    private $clientIp;

    /** @var TrustedToken|MockObject */
    private $trustedToken;

    /** @var SessionManager */
    private $storageSession;

    /** @var Auth */
    private $auth;

    protected function setUp(): void
    {
        $this->eventManager = $this->createMock(ManagerInterface::class);

        $this->credential = new class extends DataObject implements CredentialStorageInterface {
            /** @var int */
            public $loginCalls = 0;

            public function authenticate($username, $password)
            {
                return true;
            }

            public function login($username, $password)
            {
                $this->loginCalls++;
                $this->setId(7);

                return $this;
            }

            public function reload()
            {
                return $this;
            }

            public function hasAvailableResources()
            {
                return true;
            }

            public function setHasAvailableResources($hasResources)
            {
                return $this;
            }
        };
        $this->credential->setMpTfaStatus(true);

        $this->authStorage = new class implements StorageInterface {
            /** @var int */
            public $processLoginCalls = 0;

            public $user;

            public function setUser($user)
            {
                $this->user = $user;
            }

            public function getUser()
            {
                return $this->user;
            }

            public function processLogin()
            {
                $this->processLoginCalls++;
            }

            public function processLogout()
            {
            }

            public function isLoggedIn()
            {
                return false;
            }

            public function prolong()
            {
            }
        };

        $modelFactory = $this->createMock(ModelFactory::class);
        $modelFactory->method('create')->willReturn($this->credential);

        $this->response       = $this->createMock(Response::class);
        $this->actionFlag     = $this->createMock(ActionFlag::class);
        $this->storageSession = new class extends SessionManager {
            /** @var array */
            public $stored = [];

            public function __construct()
            {
            }

            public function setData($key, $value = null)
            {
                $this->stored[$key] = $value;

                return $this;
            }
        };
        $this->helperData     = $this->createMock(HelperData::class);
        $this->helperData->method('isEnabled')->willReturn(true);
        $this->helperData->method('getWhitelistIpsConfig')->willReturn(['10.0.0.1', '']);
        $this->helperData->method('checkIp')->willReturnCallback(
            static function ($ip, $range) {
                return $ip === $range;
            }
        );
        $this->helperData->method('getConfigGeneral')->willReturnMap([
            ['trust_device', null, '1'],
            ['trust_time', null, '30'],
        ]);

        $dateTime = $this->createMock(DateTime::class);
        $dateTime->method('date')->willReturn('2026-01-10 00:00:00');

        $this->trustedResource = $this->createMock(TrustedResource::class);
        $this->trustedModel    = new class {
            /** @var object */
            public $resource;

            /** @var string */
            public $createdAt = '2026-01-01 00:00:00';

            /** @var array */
            public $calls = [];

            public function getResource()
            {
                return $this->resource;
            }

            public function load($id)
            {
                return $this;
            }

            public function getCreatedAt()
            {
                return $this->createdAt;
            }

            public function setLastLogin($value)
            {
                $this->calls[] = 'setLastLogin';

                return $this;
            }

            public function save()
            {
                $this->calls[] = 'save';

                return $this;
            }

            public function delete()
            {
                $this->calls[] = 'delete';

                return $this;
            }
        };
        $this->trustedModel->resource = $this->trustedResource;
        $this->trustedFactory         = $this->createMock(TrustedFactory::class);
        $this->trustedFactory->method('create')->willReturn($this->trustedModel);

        $this->clientIp     = $this->createMock(ClientIp::class);
        $this->trustedToken = $this->createMock(TrustedToken::class);
        $this->trustedToken->method('hash')->willReturnCallback(
            static function ($token) {
                return 'h:' . $token;
            }
        );

        $url = $this->createMock(UrlInterface::class);
        $url->method('getUrl')->willReturn('http://admin/mptwofactorauth/google/authindex');

        $this->auth = new Auth(
            $this->eventManager,
            $this->createMock(BackendData::class),
            $this->authStorage,
            $this->credential,
            $this->createMock(ScopeConfigInterface::class),
            $modelFactory,
            $dateTime,
            $url,
            $this->response,
            $this->storageSession,
            $this->actionFlag,
            $this->helperData,
            $this->trustedFactory,
            $this->clientIp,
            $this->trustedToken
        );
    }

    private function allowIp(string $ip): void
    {
        $this->clientIp->method('getIp')->willReturn($ip);
    }

    private function assertTwoFactorRequired(): void
    {
        $this->response->expects($this->once())->method('setRedirect');
        $this->actionFlag->expects($this->once())->method('set');
    }

    private function loginPendingTwoFactor(): void
    {
        try {
            $this->auth->login('admin', 'secret');
        } catch (AuthenticationException $e) {
            return;
        }
    }

    private function assertPendingUserStored(): void
    {
        $this->assertSame($this->credential, $this->storageSession->stored['user']);
    }

    private function assertLoggedIn(): void
    {
        $this->response->expects($this->never())->method('setRedirect');
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('backend_auth_user_login_success');
    }

    public function testWhitelistedRemoteAddressSkipsTwoFactorAndLogsInOnce(): void
    {
        $this->allowIp('10.0.0.1');
        $this->assertLoggedIn();

        $this->auth->login('admin', 'secret');

        $this->assertSame(1, $this->authStorage->processLoginCalls);
    }

    public function testOnlyTheSingleRemoteAddressIsComparedWithWhitelist(): void
    {
        $this->allowIp('203.0.113.9');
        $this->assertTwoFactorRequired();
        $this->helperData->expects($this->atLeastOnce())
            ->method('checkIp')
            ->with('203.0.113.9', $this->anything());

        $this->loginPendingTwoFactor();

        $this->assertSame(0, $this->authStorage->processLoginCalls);
        $this->assertPendingUserStored();
    }

    public function testEmptyClientIpIsNeverWhitelisted(): void
    {
        $this->allowIp('');
        $this->assertTwoFactorRequired();
        $this->helperData->expects($this->never())->method('checkIp');

        $this->loginPendingTwoFactor();
    }

    public function testValidTrustedDeviceTokenSkipsTwoFactor(): void
    {
        $this->allowIp('203.0.113.9');
        $this->trustedToken->method('getCookie')->willReturn('tok');
        $this->trustedResource->expects($this->once())
            ->method('getTrustedIdByToken')
            ->with(7, 'h:tok')
            ->willReturn('3');
        $this->assertLoggedIn();

        $this->auth->login('admin', 'secret');

        $this->assertSame(1, $this->authStorage->processLoginCalls);
        $this->assertSame(['setLastLogin', 'save'], $this->trustedModel->calls);
    }

    public function testUnknownTokenRequiresTwoFactor(): void
    {
        $this->allowIp('203.0.113.9');
        $this->trustedToken->method('getCookie')->willReturn('other-browser');
        $this->trustedResource->method('getTrustedIdByToken')->willReturn(false);
        $this->assertTwoFactorRequired();

        $this->loginPendingTwoFactor();

        $this->assertSame(0, $this->authStorage->processLoginCalls);
    }

    public function testMissingCookieRequiresTwoFactorWithoutLookup(): void
    {
        $this->allowIp('203.0.113.9');
        $this->trustedToken->method('getCookie')->willReturn('');
        $this->trustedResource->expects($this->never())->method('getTrustedIdByToken');
        $this->assertTwoFactorRequired();

        $this->loginPendingTwoFactor();
    }

    public function testExpiredTrustedDeviceIsDeletedAndRequiresTwoFactor(): void
    {
        $this->allowIp('203.0.113.9');
        $this->trustedModel->createdAt = '2025-11-01 00:00:00';
        $this->trustedToken->method('getCookie')->willReturn('tok');
        $this->trustedResource->method('getTrustedIdByToken')->willReturn('3');
        $this->assertTwoFactorRequired();

        $this->loginPendingTwoFactor();

        $this->assertSame(['delete'], $this->trustedModel->calls);
    }

    public function testRowWithoutTokenHashIsNotMatched(): void
    {
        $this->allowIp('203.0.113.9');
        $this->trustedToken->method('getCookie')->willReturn('tok');
        $this->trustedResource->method('getTrustedIdByToken')->willReturn(false);
        $this->assertTwoFactorRequired();

        $this->loginPendingTwoFactor();
    }
}
