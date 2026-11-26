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

use Magento\Backend\Model\Session\AdminConfig;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Mageplaza\TwoFactorAuth\Model\TrustedToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TrustedTokenTest extends TestCase
{
    /** @var CookieManagerInterface|MockObject */
    private $cookieManager;

    /** @var Http|MockObject */
    private $request;

    /** @var TrustedToken */
    private $token;

    protected function setUp(): void
    {
        $this->cookieManager = $this->createMock(CookieManagerInterface::class);
        $factory = $this->createMock(CookieMetadataFactory::class);
        $factory->method('createPublicCookieMetadata')->willReturnCallback(static function () {
            return new PublicCookieMetadata();
        });
        $adminConfig = $this->createMock(AdminConfig::class);
        $adminConfig->method('getCookiePath')->willReturn('/admin');
        $this->request = $this->createMock(Http::class);

        $this->token = new TrustedToken($this->cookieManager, $factory, $adminConfig, $this->request);
    }

    public function testGenerateReturnsRandomHexAndHashIsSha256(): void
    {
        $a = $this->token->generate();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $a);
        $this->assertNotSame($a, $this->token->generate());
        $this->assertSame(hash('sha256', $a), $this->token->hash($a));
    }

    public function testCookieIsHttpOnlySecureLaxScopedToAdminPath(): void
    {
        $this->request->method('isSecure')->willReturn(true);
        $this->cookieManager->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                TrustedToken::COOKIE_NAME,
                'tok',
                $this->callback(static function (PublicCookieMetadata $meta) {
                    $data = $meta->__toArray();

                    return $data['path'] === '/admin'
                        && $data['duration'] === 30 * 86400
                        && $data['http_only'] === true
                        && $data['secure'] === true
                        && $data['samesite'] === 'Lax';
                })
            );

        $this->token->setCookie('tok', 30);
    }

    public function testGetCookieReadsConfiguredName(): void
    {
        $this->cookieManager->method('getCookie')->with(TrustedToken::COOKIE_NAME, '')->willReturn('abc');

        $this->assertSame('abc', $this->token->getCookie());
    }
}
