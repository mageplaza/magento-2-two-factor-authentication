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

namespace Mageplaza\TwoFactorAuth\Block\Adminhtml\User\Edit\Tab\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\User\Model\User;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;

/**
 * Class DisableButton
 * @package Mageplaza\TwoFactorAuth\Block\Adminhtml\User\Edit\Tab\Renderer
 */
class DisableButton extends AbstractElement
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * DisableButton constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param Registry $coreRegistry
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        Registry $coreRegistry,
        HelperData $helperData,
        $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_helperData   = $helperData;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        $this->setType('mp_tfa_disable');
    }

    /**
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getElementHtml()
    {
        /** @var $model User */
        $model        = $this->_coreRegistry->registry('mp_permissions_user');
        $isHidden     = $model->getMpTfaStatus() ? '' : 'hidden';
        $isDisabled   = $this->_helperData->getConfigGeneral('force_2fa') ? 'disabled' : '';
        $isRegistered = $model->getMpTfaStatus() ? __('This admin account has already been registered.') : '';
        $html         = '';
        $html         .= '<button id="' . $this->getHtmlId() . '" type="button" ' . $isDisabled . ' class="' . $isHidden . '">';
        $html         .= '<span>' . __('Disable Two-Factor Authentication') . '</span>';
        $html         .= '</button>';
        $html         .= '<div class="mp-success-messages mp-success">' . $isRegistered . '</div>';
        $html         .= '<div class="mp-error-messages mp-danger"></div>';

        return $html;
    }
}
