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

namespace Mageplaza\TwoFactorAuth\Controller\Adminhtml\Auto;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Model\User;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

/**
 * Class Save
 * @package Mageplaza\TwoFactorAuth\Controller\Adminhtml\Auto
 */
class Save extends Action
{
    /**
     * @var HelperData
     */
    public $helperData;

    /**
     * @var User
     */
    protected $_user;

    /**
     * @var Session
     */
    protected $_authSession;

    /**
     * @var GoogleAuthenticator
     */
    protected $_googleAuthenticator;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param HelperData $helper
     * @param User $user
     * @param Session $authSession
     * @param GoogleAuthenticator $googleAuthenticator
     */
    public function __construct(
        Context $context,
        HelperData $helper,
        User $user,
        Session $authSession,
        GoogleAuthenticator $googleAuthenticator
    ) {
        $this->helperData           = $helper;
        $this->_user                = $user;
        $this->_authSession         = $authSession;
        $this->_googleAuthenticator = $googleAuthenticator;

        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        // TODO: Implement execute() method.
        if ($this->getRequest()->isAjax()) {
            $data = $this->getRequest()->getParams();
            if ($this->helperData->isEnabled()) {
                try {
                    if (empty($data['secret_code'])
                        || empty($data['confirm_code'])
                        || !$this->_googleAuthenticator->checkCode($data['secret_code'], $data['confirm_code'])
                    ) {
                        throw new LocalizedException(__('Invalid Code'));
                    }
                    $this->_user->load($this->_authSession->getUser()->getId());
                    $this->_user->setMpTfaEnable('1');
                    $this->_user->setMpTfaSecret($data['secret_code']);
                    $this->_user->setMpTfaStatus('1');
                    $this->_user->save();
                    $result = ['notify' => 'success'];
                } catch (Exception $e) {
                    $result = ['notify' => $e->getMessage()];
                }

                return $this->getResponse()->representJson(HelperData::jsonEncode($result));
            }
        }
    }
}
