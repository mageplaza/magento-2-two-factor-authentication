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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;
use Mageplaza\TwoFactorAuth\Model\TwoFactorLockout;

/**
 * Class AuthIndex
 * @package Mageplaza\TwoFactorAuth\Controller\Adminhtml\Google
 */
class AuthIndex extends Action
{
    /**
     * Page result factory
     *
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var SessionManager
     */
    protected $_storageSession;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var TwoFactorLockout
     */
    protected $_lockout;

    /**
     * AuthIndex constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SessionManager $storageSession
     * @param HelperData $helperData
     * @param TwoFactorLockout $lockout
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SessionManager $storageSession,
        HelperData $helperData,
        TwoFactorLockout $lockout
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_storageSession   = $storageSession;
        $this->_helperData       = $helperData;
        $this->_lockout          = $lockout;

        parent::__construct($context);
    }

    /**
     * execute the action
     *
     * @return \Magento\Backend\Model\View\Result\Page|Page|Redirect
     */
    public function execute()
    {
        $user = $this->_storageSession->getData('user');
        if ($user && $this->_lockout->isLocked($this->_lockout->reload($user))) {
            $this->_storageSession->unsetData('user');
            $this->messageManager->addErrorMessage(__(
                'The account sign-in was incorrect or your account is disabled temporarily. '
                . 'Please wait and try again later.'
            ));

            return $this->resultRedirectFactory->create()->setPath('adminhtml/auth/login');
        }

        return $this->resultPageFactory->create();
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return (bool) $this->_storageSession->getData('user');
    }
}
