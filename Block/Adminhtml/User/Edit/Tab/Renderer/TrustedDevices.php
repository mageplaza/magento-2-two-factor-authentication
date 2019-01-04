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
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\TwoFactorAuth\Block\Adminhtml\User\Edit\Tab\Renderer;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Mageplaza\TwoFactorAuth\Model\TrustedFactory;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;

/**
 * Class TrustedDevices
 * @package Mageplaza\TwoFactorAuth\Block\Adminhtml\User\Edit\Tab\Renderer
 */
class TrustedDevices extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_TwoFactorAuth::user/form/trusted-devices.phtml';

    /**
     * @var TrustedFactory
     */
    protected $_trustedFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * TrustedDevices constructor.
     *
     * @param Context        $context
     * @param TrustedFactory $trustedFactory
     * @param HelperData     $helperData
     * @param array          $data
     */
    public function __construct(
        Context $context,
        TrustedFactory $trustedFactory,
        HelperData $helperData,
        array $data = []
    )
    {
        $this->_trustedFactory = $trustedFactory;
        $this->_helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mageplaza\TwoFactorAuth\Model\ResourceModel\Trusted\Collection
     */
    public function getTrustedCollection()
    {
        return $this->_helperData->getTrustedCollection($this->getUserObject()->getUserId());
    }
}