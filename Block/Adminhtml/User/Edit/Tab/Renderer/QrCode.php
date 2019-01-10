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

use PHPGangsta\GoogleAuthenticator;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * QrCode constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param Registry $coreRegistry
     * @param GoogleAuthenticator $googleAuthenticator
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        Registry $coreRegistry,
        GoogleAuthenticator $googleAuthenticator,
        StoreManagerInterface $storeManager,
        $data = []
    )
    {
        $this->_coreRegistry        = $coreRegistry;
        $this->_googleAuthenticator = $googleAuthenticator;
        $this->_storeManager        = $storeManager;

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
        $model       = $this->_coreRegistry->registry('mp_permissions_user');
        $secret      = $this->getValue();
        $userEmail   = $model->getEmail();
        $storeUrl    = $this->_storeManager->getStore()->getBaseUrl();
        $accountName = $storeUrl . ':' . $userEmail;
        $qrCodeUrl   = $this->_googleAuthenticator->getQRCodeGoogleUrl($accountName, $secret);
        $html        = '';
        $html        .= '<div class="mp-tfa-qrcode-img">';
        $html        .= '<img src="' . $qrCodeUrl . '" alt="' . __('Qr Code Image') . '" />';
        $html        .= '</div><div class="mp-tfa-qrcode-description mp-bg-light">';
        $html        .= '<p>' . __("Can not scan the code?") . '<br>'
            . __("You can add the entry manually, please provide the following details to the application on your phone.") . '<br>';
        if ($userEmail) {
            $html .= __("Account: ") . $accountName . '<br>';
        }
        $html .= __("Key: ") . $secret . '<br>'
            . __("Time based: Yes") . '</p>';
        $html .= '</div>';

        return $html;
    }
}