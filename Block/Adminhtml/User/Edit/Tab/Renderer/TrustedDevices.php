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

use Exception;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;
use Mageplaza\TwoFactorAuth\Model\ResourceModel\Trusted\Collection;

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
     * @var HelperData
     */
    protected $_helperData;

    /**
     * TrustedDevices constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        array $data = []
    ) {
        $this->_helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @return Collection
     */
    public function getTrustedCollection()
    {
        return $this->_helperData->getTrustedCollection($this->getUserObject()->getUserId());
    }

    /**
     * @param $date
     *
     * @return string
     * @throws Exception
     */
    public function getFormattedDate($date)
    {
        $convertedDate = $this->_helperData->convertTimeZone($date);

        return $convertedDate->format('Y-m-d H:i:s');
    }

    /**
     * @param $trustedId
     *
     * @return string
     */
    public function getDeleteUrl($trustedId)
    {
        return $this->getUrl('mptwofactorauth/google/deletetrusted', ['trusted_id' => $trustedId]);
    }
}
