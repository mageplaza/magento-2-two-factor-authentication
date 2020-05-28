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
use Magento\Framework\Session\SessionManager;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;

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
     * AuthIndex constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SessionManager $storageSession
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SessionManager $storageSession,
        HelperData $helperData
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_storageSession   = $storageSession;
        $this->_helperData       = $helperData;

        parent::__construct($context);
    }

    /**
     * execute the action
     *
     * @return \Magento\Backend\Model\View\Result\Page|Page
     */
    public function execute()
    {
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
