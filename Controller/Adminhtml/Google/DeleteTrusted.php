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
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Controller\Result\Redirect;
use Mageplaza\TwoFactorAuth\Model\TrustedFactory;

/**
 * Class Delete
 * @package Mageplaza\Blog\Controller\Adminhtml\Post
 */
class DeleteTrusted extends Action
{
    /**
     * @var TrustedFactory
     */
    protected $_trustedFactory;

    /**
     * @var Session
     */
    protected $_authSession;

    /**
     * DeleteTrusted constructor.
     *
     * @param Context $context
     * @param TrustedFactory $trustedFactory
     * @param Session $authSession
     */
    public function __construct(
        Context $context,
        TrustedFactory $trustedFactory,
        Session $authSession
    ) {
        $this->_trustedFactory = $trustedFactory;
        $this->_authSession    = $authSession;

        parent::__construct($context);
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($trustedId = $this->getRequest()->getParam('trusted_id')) {
            try {
                $trusted = $this->_trustedFactory->create()->load($trustedId);
                $userId  = (int) $this->_authSession->getUser()->getId();
                if (!$trusted->getId() || (int) $trusted->getUserId() !== $userId) {
                    $this->messageManager->addErrorMessage(__('The selected Trusted Device to delete was not found.'));
                    $resultRedirect->setPath('adminhtml/system_account/index');

                    return $resultRedirect;
                }
                $trusted->delete();

                $this->messageManager->addSuccessMessage(__('The selected Trusted Device has been deleted.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('adminhtml/system_account/index');

                return $resultRedirect;
            }
        } else {
            $this->messageManager->addErrorMessage(__('The selected Trusted Device to delete was not found.'));
        }

        $resultRedirect->setPath('adminhtml/system_account/index');

        return $resultRedirect;
    }
}
