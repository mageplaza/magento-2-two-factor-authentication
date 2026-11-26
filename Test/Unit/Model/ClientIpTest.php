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

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddressFactory;
use Mageplaza\TwoFactorAuth\Model\ClientIp;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClientIpTest extends TestCase
{
    /** @var RemoteAddressFactory|MockObject */
    private $factoryMock;

    protected function setUp(): void
    {
        $this->factoryMock = $this->createMock(RemoteAddressFactory::class);
    }

    private function mockRemoteAddress($value, array $headers = [], ?array $proxies = null): void
    {
        $remoteAddress = $this->createMock(RemoteAddress::class);
        $remoteAddress->method('getRemoteAddress')->willReturn($value);
        $this->factoryMock->expects($this->once())
            ->method('create')
            ->with(['alternativeHeaders' => $headers, 'trustedProxies' => $proxies])
            ->willReturn($remoteAddress);
    }

    public function testReturnsValidIp(): void
    {
        foreach (['10.0.0.1', '203.0.113.7', '2001:db8::1'] as $ip) {
            $this->setUp();
            $this->mockRemoteAddress($ip);

            $this->assertSame($ip, (new ClientIp($this->factoryMock))->getIp());
        }
    }

    public function testReturnsEmptyStringForInvalidValue(): void
    {
        foreach (['1.2.3.4,5.6.7.8', '<svg onload=alert(1)>', 'garbage', '', false] as $value) {
            $this->setUp();
            $this->mockRemoteAddress($value);

            $this->assertSame('', (new ClientIp($this->factoryMock))->getIp());
        }
    }

    public function testPassesConfiguredHeadersAndProxies(): void
    {
        $this->mockRemoteAddress('198.51.100.4', ['HTTP_X_FORWARDED_FOR'], ['10.0.0.0/8']);

        $clientIp = new ClientIp($this->factoryMock, ['HTTP_X_FORWARDED_FOR'], ['10.0.0.0/8']);

        $this->assertSame('198.51.100.4', $clientIp->getIp());
    }
}
