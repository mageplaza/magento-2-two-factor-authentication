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

/**
 * Class DisableButton
 * @package Mageplaza\TwoFactorAuth\Block\Adminhtml\User\Edit\Tab\Renderer
 */
class DisableButton extends AbstractElement
{
    /**
     * DisableButton constructor.
     *
     * @param Factory           $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper           $escaper
     * @param array             $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    )
    {
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
        $html = '';
        $html .= '<button id="' . $this->getHtmlId() . '" type="button">';
        $html .= '<span>' . __('Disable two-factor authentication') . '</span>';
        $html .= '</button>';

        return $html;
    }
}