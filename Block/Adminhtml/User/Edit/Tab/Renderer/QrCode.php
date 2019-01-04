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
 * Class QrCode
 * @package Mageplaza\TwoFactorAuth\Block\Adminhtml\User\Edit\Tab\Renderer
 */
class QrCode extends AbstractElement
{
    /**
     * QrCode constructor.
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

        $this->setType('mp_tfa_qr_code');
    }

    /**
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getElementHtml()
    {
        $html = '';
        $html .= '<div class="mp-tfa-qrcode-img">';
        $html .= '<img src="https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2FBlog%3Fsecret%3DRQDYOERHIEAOF76A" alt="Qr Code Image" />';
        $html .= '</div><div class="mp-tfa-qrcode-description mp-bg-light">';
        $html .= '<p>' . __("Can't scan the code?") . '<br>'
            . __("To add the entry manually, provide the following details to the application on your phone.") . '<br>'
            . __("Account: example@gmail.com") . '<br>'
            . __("Key: ABC XYZ") . '<br>'
            . __("Time based: Yes") . '</p>';
        $html .= '</div>';

        return $html;
    }
}