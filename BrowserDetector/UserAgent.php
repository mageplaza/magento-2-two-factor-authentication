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

namespace Mageplaza\TwoFactorAuth\BrowserDetector;

class UserAgent
{
    /**
     * @var string
     */
    private $userAgentString;

    /**
     * @param string $userAgentString
     */
    public function __construct($userAgentString = null)
    {
        if (null !== $userAgentString) {
            $this->setUserAgentString($userAgentString);
        }
    }

    /**
     * @param string $userAgentString
     *
     * @return $this
     */
    public function setUserAgentString($userAgentString)
    {
        $this->userAgentString = (string)$userAgentString;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgentString()
    {
        if (null === $this->userAgentString) {
            $this->createUserAgentString();
        }

        return $this->userAgentString;
    }

    /**
     * @return string
     */
    public function createUserAgentString()
    {
        $userAgentString = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        $this->setUserAgentString($userAgentString);

        return $userAgentString;
    }
}
