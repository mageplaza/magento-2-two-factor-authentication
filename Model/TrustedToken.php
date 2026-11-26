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

use Magento\Backend\Model\Session\AdminConfig;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;

class TrustedToken
{
    const COOKIE_NAME = 'mp_tfa_trusted';

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var AdminConfig
     */
    protected $adminConfig;

    /**
     * @var Http
     */
    protected $request;

    /**
     * TrustedToken constructor.
     *
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param AdminConfig $adminConfig
     * @param Http $request
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        AdminConfig $adminConfig,
        Http $request
    ) {
        $this->cookieManager         = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->adminConfig           = $adminConfig;
        $this->request               = $request;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function generate(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * @param string $token
     *
     * @return string
     */
    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * @return string
     */
    public function getCookie(): string
    {
        return (string) $this->cookieManager->getCookie(self::COOKIE_NAME, '');
    }

    /**
     * @param string $token
     * @param int $days
     *
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws LocalizedException
     */
    public function setCookie(string $token, int $days): void
    {
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($days * 86400)
            ->setPath((string) $this->adminConfig->getCookiePath())
            ->setHttpOnly(true)
            ->setSecure($this->request->isSecure())
            ->setSameSite('Lax');

        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $token, $metadata);
    }
}
