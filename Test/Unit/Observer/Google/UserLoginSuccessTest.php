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

namespace Mageplaza\TwoFactorAuth\Test\Unit\Observer\Google;

use Magento\Backend\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;
use Mageplaza\TwoFactorAuth\Model\ClientIp;
use Mageplaza\TwoFactorAuth\Model\TrustedFactory;
use Mageplaza\TwoFactorAuth\Model\TrustedToken;
use Mageplaza\TwoFactorAuth\Observer\Google\UserLoginSuccess;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UserLoginSuccessTest extends TestCase
{
    /** @var DateTime|MockObject */
    private $dateTimeMock;

    /** @var ManagerInterface|MockObject */
    private $messageManagerMock;

    /** @var TrustedFactory|MockObject */
    private $trustedFactoryMock;

    /** @var ClientIp|MockObject */
    private $clientIpMock;

    /** @var TrustedToken|MockObject */
    private $trustedTokenMock;

    /** @var HelperData|MockObject */
    private $helperMock;

    /** @var UserLoginSuccess */
    private $observer;

    protected function setUp(): void
    {
        $this->dateTimeMock        = $this->createMock(DateTime::class);
        $this->messageManagerMock  = $this->createMock(ManagerInterface::class);
        $this->trustedFactoryMock  = $this->createMock(TrustedFactory::class);
        $this->clientIpMock        = $this->createMock(ClientIp::class);
        $this->trustedTokenMock    = $this->createMock(TrustedToken::class);
        $this->helperMock          = $this->createMock(HelperData::class);

        $this->observer = new UserLoginSuccess(
            $this->dateTimeMock,
            $this->messageManagerMock,
            $this->trustedFactoryMock,
            $this->helperMock,
            $this->clientIpMock,
            $this->trustedTokenMock
        );
    }

    public function testExecuteDoesNothingWhenNotTrusted(): void
    {
        $this->trustedFactoryMock->expects($this->never())->method('create');

        $this->observer->execute(new Observer(['event' => new Event()]));
    }

    public function testExecuteSavesTrustedDeviceWithHashedToken(): void
    {
        $this->clientIpMock->method('getIp')->willReturn('10.0.0.1');
        $this->dateTimeMock->method('date')->willReturn('2026-01-01 00:00:00');
        $this->helperMock->method('getDeviceName')->willReturn('My Device');

        $trusted = new class {
            /** @var array */
            public $data = [];

            public function setDeviceIp($value)
            {
                $this->data['device_ip'] = $value;

                return $this;
            }

            public function setLastLogin($value)
            {
                $this->data['last_login'] = $value;

                return $this;
            }

            public function setCreatedAt($value)
            {
                $this->data['created_at'] = $value;

                return $this;
            }

            public function setTokenHash($value)
            {
                $this->data['token_hash'] = $value;

                return $this;
            }

            public function load($id)
            {
                $this->data['loaded'] = $id;

                return $this;
            }

            public function setName($value)
            {
                $this->data['name'] = $value;

                return $this;
            }

            public function setUserId($value)
            {
                $this->data['user_id'] = $value;

                return $this;
            }

            public function save()
            {
                $this->data['saved'] = true;

                return $this;
            }
        };
        $this->trustedTokenMock->method('generate')->willReturn('plain-token');
        $this->trustedTokenMock->method('hash')->with('plain-token')->willReturn('hashed-token');
        $this->helperMock->method('getConfigGeneral')->with('trust_time')->willReturn('30');
        $this->trustedTokenMock->expects($this->once())->method('setCookie')->with('plain-token', 30);
        $this->trustedFactoryMock->expects($this->once())->method('create')->willReturn($trusted);

        $user  = new DataObject(['user_name' => 'admin', 'id' => 7]);
        $event = new Event(['user' => $user, 'mp_is_trusted' => true]);
        $observer = new Observer(['user' => $user, 'event' => $event]);

        $this->observer->execute($observer);

        $this->assertSame('10.0.0.1', $trusted->data['device_ip']);
        $this->assertSame('hashed-token', $trusted->data['token_hash']);
        $this->assertSame('2026-01-01 00:00:00', $trusted->data['created_at']);
        $this->assertArrayNotHasKey('loaded', $trusted->data);
        $this->assertSame('My Device', $trusted->data['name']);
        $this->assertSame(7, $trusted->data['user_id']);
        $this->assertTrue($trusted->data['saved']);
    }
}
