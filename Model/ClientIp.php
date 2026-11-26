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

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddressFactory;

class ClientIp
{
    /**
     * @var RemoteAddressFactory
     */
    protected $remoteAddressFactory;

    /**
     * @var array
     */
    protected $alternativeHeaders;

    /**
     * @var array|null
     */
    protected $trustedProxies;

    /**
     * ClientIp constructor.
     *
     * @param RemoteAddressFactory $remoteAddressFactory
     * @param array $alternativeHeaders
     * @param array|null $trustedProxies
     */
    public function __construct(
        RemoteAddressFactory $remoteAddressFactory,
        array $alternativeHeaders = [],
        ?array $trustedProxies = null
    ) {
        $this->remoteAddressFactory = $remoteAddressFactory;
        $this->alternativeHeaders   = $alternativeHeaders;
        $this->trustedProxies       = $trustedProxies;
    }

    public function getIp(): string
    {
        $ip = (string) $this->remoteAddressFactory->create([
            'alternativeHeaders' => $this->alternativeHeaders,
            'trustedProxies'     => $this->trustedProxies
        ])->getRemoteAddress();

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }
}
