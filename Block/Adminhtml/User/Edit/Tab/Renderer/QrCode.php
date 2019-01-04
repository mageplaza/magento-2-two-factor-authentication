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
use PHPGangsta\GoogleAuthenticator;

/**
 * Class QrCode
 * @package Mageplaza\TwoFactorAuth\Block\Adminhtml\User\Edit\Tab\Renderer
 */
class QrCode extends AbstractElement
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var \PHPGangsta\GoogleAuthenticator
     */
    protected $_googleAuthenticator;

    /**
     * QrCode constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        Registry $coreRegistry,
        GoogleAuthenticator $googleAuthenticator,
        $data = []
    )
    {
        $this->_coreRegistry        = $coreRegistry;
        $this->_googleAuthenticator = $googleAuthenticator;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        $this->setType('mp_tfa_secret_temp');
    }

    /**
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getElementHtml()
    {
        /** @var $model \Magento\User\Model\User */
        $model     = $this->_coreRegistry->registry('permissions_user');
        $secret    = $this->getValue();
        $userEmail = $model->getEmail();
        $qrCodeUrl = $this->_googleAuthenticator->getQRCodeGoogleUrl($userEmail, $secret);
        $html      = '';
        $html      .= '<div class="mp-tfa-qrcode-img">';
        $html      .= '<img src="' . $qrCodeUrl . '" alt="' . __('Qr Code Image') . '" />';
        $html      .= '</div><div class="mp-tfa-qrcode-description mp-bg-light">';
        $html      .= '<p>' . __("Can't scan the code?") . '<br>'
            . __("To add the entry manually, provide the following details to the application on your phone.") . '<br>';
        if ($userEmail){
            $html      .= __("Account: ") . $userEmail . '<br>';
        }
        $html      .= __("Key: ") . $secret . '<br>'
            . __("Time based: Yes") . '</p>';
        $html .= '</div>';

        return $html;
    }
}