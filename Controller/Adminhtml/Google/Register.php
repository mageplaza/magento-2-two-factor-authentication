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

use PHPGangsta\GoogleAuthenticator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\User\Model\UserFactory;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;

/**
 * Class Register
 * @package Mageplaza\TwoFactorAuth\Controller\Adminhtml\Google
 */
class Register extends Action
{
    /**
     * @var \PHPGangsta\GoogleAuthenticator
     */
    protected $_googleAuthenticator;

    /**
     * User model factory
     *
     * @var \Magento\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Register constructor.
     *
     * @param Context             $context
     * @param GoogleAuthenticator $googleAuthenticator
     * @param UserFactory         $userFactory
     * @param HelperData          $helperData
     */
    public function __construct(
        Context $context,
        GoogleAuthenticator $googleAuthenticator,
        UserFactory $userFactory,
        HelperData $helperData
    )
    {
        $this->_googleAuthenticator = $googleAuthenticator;
        $this->_userFactory         = $userFactory;
        $this->_helperData          = $helperData;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data         = $this->getRequest()->getParams();
        $inputOneCode = $data['confirm_code'];
        $userId       = $data['user_id'];
        $secretCode   = $data['secret_code'];

        $checkResult = $this->_googleAuthenticator->verifyCode($secretCode, $inputOneCode, 1);
        if ($checkResult) {
            try {
                /** @var $model \Magento\User\Model\User */
                $model = $this->_userFactory->create()->load($userId);
                $model->setMpTfaSecret($secretCode)
                    ->setMpTfaStatus(1)
                    ->save();
                $result = ['status' => 'valid'];
            } catch (\Exception $e) {
                $result = ['status' => 'error', 'error' => $e->getMessage()];
            }

        } else {
            $result = ['status' => 'invalid'];
        }

        return $this->getResponse()->representJson(HelperData::jsonEncode($result));
    }
}
