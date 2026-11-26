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

namespace Mageplaza\TwoFactorAuth\Test\Unit\Controller\Adminhtml\Google;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Auth\StorageInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Session\SessionManager;
use Magento\Security\Model\AdminSessionsManager;
use Mageplaza\TwoFactorAuth\Controller\Adminhtml\Google\AuthPost;
use Mageplaza\TwoFactorAuth\Model\TrustedFactory;
use Mageplaza\TwoFactorAuth\Model\TwoFactorLockout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthPostTest extends TestCase
{
    private const SECRET = 'ABCDEFGHIJKLMNOP';

    /** @var GoogleAuthenticator */
    private $authenticator;

    /** @var string */
    private $authCode = '';

    /** @var TwoFactorLockout|MockObject */
    private $lockout;

    /** @var SessionManager */
    private $session;

    /** @var MessageManager|MockObject */
    private $messages;

    /** @var object */
    private $authStorage;

    /** @var string[] */
    private $redirects = [];

    /** @var ManagerInterface|MockObject */
    private $eventManager;

    /** @var AuthPost */
    private $controller;

    /** @var DataObject */
    private $pendingUser;

    protected function setUp(): void
    {
        $this->redirects = [];
        $this->pendingUser = new DataObject(['id' => 7, 'user_name' => 'admin', 'mp_tfa_secret' => self::SECRET]);

        $this->session = new class extends SessionManager {
            /** @var array */
            public $stored = [];

            public function __construct()
            {
            }

            public function getData($key = '', $clear = false)
            {
                return $this->stored[$key] ?? null;
            }

            public function setData($key, $value = null)
            {
                $this->stored[$key] = $value;

                return $this;
            }

            public function unsetData($key = null)
            {
                unset($this->stored[$key]);

                return $this;
            }
        };
        $this->session->setData('user', $this->pendingUser);

        $this->authStorage = new class implements StorageInterface {
            /** @var int */
            public $processLoginCalls = 0;

            public function setUser($user)
            {
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
        $auth = $this->createMock(Auth::class);
        $auth->method('getAuthStorage')->willReturn($this->authStorage);

        $request = $this->createMock(Http::class);
        $request->method('getParams')->willReturnCallback(function () {
            return ['auth-code' => $this->authCode];
        });

        $this->messages     = $this->createMock(MessageManager::class);
        $this->eventManager = $this->createMock(ManagerInterface::class);

        $backendUrl = $this->createMock(UrlInterface::class);
        $backendUrl->method('getStartupPageUrl')->willReturn('adminhtml/dashboard');

        $redirectFactory = $this->createMock(RedirectFactory::class);
        $redirectFactory->method('create')->willReturnCallback(function () {
            $redirect = $this->createMock(Redirect::class);
            $redirect->method('setPath')->willReturnCallback(function ($path) use ($redirect) {
                $this->redirects[] = $path;

                return $redirect;
            });

            return $redirect;
        });

        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($request);
        $context->method('getAuth')->willReturn($auth);
        $context->method('getBackendUrl')->willReturn($backendUrl);
        $context->method('getEventManager')->willReturn($this->eventManager);
        $context->method('getMessageManager')->willReturn($this->messages);
        $context->method('getResultRedirectFactory')->willReturn($redirectFactory);
        $context->method('getHelper')->willReturn($this->createMock(BackendHelper::class));

        $this->authenticator = new GoogleAuthenticator();
        $this->lockout       = $this->createMock(TwoFactorLockout::class);
        $currentUser         = $this->createMock(\Magento\User\Model\User::class);
        $this->lockout->method('reload')->willReturn($currentUser);

        $sessionsManager = $this->createMock(AdminSessionsManager::class);
        $sessionsManager->method('getCurrentSession')->willReturn(
            $this->createMock(\Magento\Security\Model\AdminSessionInfo::class)
        );

        $this->controller = new AuthPost(
            $context,
            $this->authenticator,
            $this->session,
            $sessionsManager,
            $this->createMock(RemoteAddress::class),
            $this->createMock(TrustedFactory::class),
            $this->lockout
        );
    }

    private function wrongCode(): string
    {
        $code = $this->authenticator->getCode(self::SECRET);
        do {
            $wrong = sprintf('%06d', random_int(0, 999999));
        } while ($this->authenticator->checkCode(self::SECRET, $wrong) || $wrong === $code);

        return $wrong;
    }

    public function testLockedPendingUserIsClearedWithoutCheckingCode(): void
    {
        $this->lockout->method('isLocked')->willReturn(true);
        $this->authCode = $this->authenticator->getCode(self::SECRET);
        $this->lockout->expects($this->never())->method('recordFailure');
        $this->messages->expects($this->once())->method('addErrorMessage');

        $this->controller->execute();

        $this->assertNull($this->session->getData('user'));
        $this->assertSame(['adminhtml/auth/login'], $this->redirects);
    }

    public function testWrongCodeRecordsFailureAndKeepsPendingUser(): void
    {
        $this->lockout->method('isLocked')->willReturn(false);
        $this->authCode = $this->wrongCode();
        $this->lockout->expects($this->once())->method('recordFailure')->willReturn(false);
        $this->lockout->expects($this->never())->method('reset');

        $this->controller->execute();

        $this->assertSame($this->pendingUser, $this->session->getData('user'));
        $this->assertSame(['mptwofactorauth/google/authindex'], $this->redirects);
        $this->assertSame(0, $this->authStorage->processLoginCalls);
    }

    public function testWrongCodeThatLocksAccountClearsPendingUser(): void
    {
        $this->lockout->method('isLocked')->willReturn(false);
        $this->authCode = $this->wrongCode();
        $this->lockout->expects($this->once())->method('recordFailure')->willReturn(true);

        $this->controller->execute();

        $this->assertNull($this->session->getData('user'));
        $this->assertSame(['adminhtml/auth/login'], $this->redirects);
    }

    public function testCorrectCodeResetsFailuresAndLogsIn(): void
    {
        $this->lockout->method('isLocked')->willReturn(false);
        $this->authCode = $this->authenticator->getCode(self::SECRET);
        $this->lockout->expects($this->once())->method('reset');
        $this->lockout->expects($this->never())->method('recordFailure');

        $this->controller->execute();

        $this->assertSame(1, $this->authStorage->processLoginCalls);
        $this->assertSame(['adminhtml/dashboard'], $this->redirects);
    }
}
