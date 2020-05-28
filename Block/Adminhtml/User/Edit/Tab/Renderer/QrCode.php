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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\User;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;

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
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * QrCode constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param HelperData $helperData
     * @param RequestInterface $request
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        HelperData $helperData,
        RequestInterface $request,
        $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_storeManager = $storeManager;
        $this->_helperData   = $helperData;
        $this->request       = $request;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        $this->setType('mp_tfa_secret_temp');
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        /** @var $model User */
        $user = $this->_coreRegistry->registry('mp_permissions_user');

        $secret      = $this->getValue();
        $userEmail   = $user->getEmail();
        $accountName = $this->request->getHttpHost() . ':' . $userEmail;

        $description  = __('Please download the authentication app (such as Authy, Google Authentication
        ) to scan this QR code.') . '</p>';
        $img          = $this->_helperData->generateUri($this->getUri($accountName, $secret, $userEmail));
        $info1        = __('Cannot scan the code?');
        $info2        = __('You can add the entry manually
        , please provide the following details to the application on your phone.');
        $info3        = __('Account: %1', $accountName);
        $info4        = __('Key: %1', implode(' ', str_split($secret, 4)));
        $info5        = __('Time based: Yes');
        $confirmLabel = __('Confirmation Code');
        $confirmNote  = __('Use the code provided by your authentication app.');
        $buttonLabel  = __('Register');

        $html = <<<HTML
<div class="mp-tfa">
    <p>{$description}</p>
    <div class="mp-tfa-qrcode">
        <div class="mp-tfa-qrcode-img">{$img}</div>
        <div class="mp-tfa-qrcode-description mp-bg-light">
            <p>
                {$info1}<br>
                {$info2}<br>
                {$info3}<br>
                {$info4}<br>
                {$info5}
            </p>
        </div>
        <div style="clear: both"></div>
    </div>
    <div class="mp-tfa-validate">
        <label for="mp_tfa_one_code" class="mp-tfa-validate-label">{$confirmLabel}</label>
        <div class="mp-tfa-validate-code">
            <input id="mp_tfa_one_code" name="mp_tfa_one_code" title="Confirmation Code"
             type="text" class="mp-tfa-validate-input input-text admin__control-text">
            <button type="button" id="mp_tfa_register" class="mp_tfa_register primary">
                <span class="mp-white">{$buttonLabel}</span>
            </button>
            <div style="clear: both"></div>
        </div>
        <div class="note admin__field-note" id="mp_tfa_one_code-note">{$confirmNote}</div>
    </div>
</div>
HTML;

        return $html;
    }

    /**
     * @param $label
     * @param $secretKey
     * @param $issuer
     *
     * @return string
     */
    public function getUri($label, $secretKey, $issuer)
    {
        return 'otpauth://totp/' . rawurlencode($label) . '?secret=' . $secretKey . '&issuer=' . rawurlencode($issuer);
    }
}
