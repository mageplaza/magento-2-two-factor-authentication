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

class AcceptLanguage
{
    /**
     * @var string
     */
    private $acceptLanguageString;

    /**
     * @param string $acceptLanguageString
     */
    public function __construct($acceptLanguageString = null)
    {
        if (null !== $acceptLanguageString) {
            $this->setAcceptLanguageString($acceptLanguageString);
        }
    }

    /**
     * @param string $acceptLanguageString
     *
     * @return $this
     */
    public function setAcceptLanguageString($acceptLanguageString)
    {
        $this->acceptLanguageString = $acceptLanguageString;

        return $this;
    }

    /**
     * @return string
     */
    public function getAcceptLanguageString()
    {
        if (null === $this->acceptLanguageString) {
            $this->createAcceptLanguageString();
        }

        return $this->acceptLanguageString;
    }

    /**
     * @return string
     */
    public function createAcceptLanguageString()
    {
        $acceptLanguageString = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;
        $this->setAcceptLanguageString($acceptLanguageString);

        return $acceptLanguageString;
    }
}
