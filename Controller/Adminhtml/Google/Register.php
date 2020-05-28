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

namespace Mageplaza\TwoFactorAuth\Controller\Adminhtml\Google;

use Exception;
use Google\Authenticator\GoogleAuthenticator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;
use Psr\Cache\InvalidArgumentException;

/**
 * Class Register
 * @package Mageplaza\TwoFactorAuth\Controller\Adminhtml\Google
 */
class Register extends Action
{
    /**
     * @var GoogleAuthenticator
     */
    protected $_googleAuthenticator;

    /**
     * Register constructor.
     *
     * @param Context $context
     * @param GoogleAuthenticator $googleAuthenticator
     */
    public function __construct(
        Context $context,
        GoogleAuthenticator $googleAuthenticator
    ) {
        $this->_googleAuthenticator = $googleAuthenticator;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws InvalidArgumentException
     */
    public function execute()
    {
        $data         = $this->getRequest()->getParams();
        $inputOneCode = $data['confirm_code'];
        $secretCode   = $data['secret_code'];

        try {
            $checkResult = $this->_googleAuthenticator->checkCode($secretCode, $inputOneCode);
            if ($checkResult) {
                $result = ['status' => 'valid', 'secret_code' => $secretCode];
            } else {
                $result = ['status' => 'invalid'];
            }
        } catch (Exception $e) {
            $result = ['status' => 'error', 'error' => $e->getMessage()];
        }

        return $this->getResponse()->representJson(HelperData::jsonEncode($result));
    }
}
