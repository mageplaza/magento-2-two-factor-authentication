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

namespace Mageplaza\TwoFactorAuth\Plugin\Adminhtml\Block\System\Account\Edit;

use Google\Authenticator\GoogleAuthenticator;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\User\Model\UserFactory;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;

/**
 * Class Form
 * @package Mageplaza\TwoFactorAuth\Plugin\Block\Adminhtml\System\Account\Edit
 */
class Form
{
	/**
	 * @var Enabledisable
	 */
	protected $_enableDisable;

	/**
	 * @var Registry
	 */
	protected $_coreRegistry;

	/**
	 * @var LayoutInterface
	 */
	protected $_layout;

	/**
	 * @var Session
	 */
	protected $_authSession;

	/**
	 * @var UserFactory
	 */
	protected $_userFactory;

	/**
	 * @var HelperData
	 */
	protected $_helperData;

	/**
	 * Form constructor.
	 *
	 * @param \Magento\Config\Model\Config\Source\Enabledisable $enableDisable
	 * @param \Magento\Framework\Registry $coreRegistry
	 * @param \Magento\Framework\View\LayoutInterface $layout
	 * @param \Magento\Backend\Model\Auth\Session $authSession
	 * @param \Magento\User\Model\UserFactory $userFactory
	 * @param \Mageplaza\TwoFactorAuth\Helper\Data $helperData
	 */
	public function __construct(
		Enabledisable $enableDisable,
		Registry $coreRegistry,
		LayoutInterface $layout,
		Session $authSession,
		UserFactory $userFactory,
		HelperData $helperData
	)
	{
		$this->_enableDisable         = $enableDisable;
		$this->_coreRegistry          = $coreRegistry;
		$this->_layout                = $layout;
		$this->_authSession           = $authSession;
		$this->_userFactory           = $userFactory;
		$this->_helperData            = $helperData;
	}

	/**
	 * @param \Magento\Backend\Block\System\Account\Edit\Form $subject
	 * @param \Closure $proceed
	 *
	 * @return mixed
	 */
	public function aroundGetFormHtml(
		\Magento\Backend\Block\System\Account\Edit\Form $subject,
		\Closure $proceed
	)
	{
		$form = $subject->getForm();
		/** @var $model \Magento\User\Model\User */
		$userId = $this->_authSession->getUser()->getId();
		$user   = $this->_userFactory->create()->load($userId);
		$user->unsetData('password');
		$this->_coreRegistry->register('mp_permissions_user', $user);
        $secretFactory = new GoogleAuthenticator();
		$secret = ($user->getMpTfaSecret()) ?: $secretFactory->generateSecret();
		if (is_object($form) && $this->_helperData->isEnabled()) {
			$mpTfaFieldset = $form->addFieldset('mp_tfa_security', ['legend' => __('Security')]);
			$mpTfaFieldset->addField(
				'mp_tfa_enable',
				'select',
				[
					'name'   => 'mp_tfa_enable',
					'label'  => __('Enable 2FA'),
					'title'  => __('Enable 2FA'),
					'values' => $this->_enableDisable->toOptionArray(),
					'note'   => 'Please download the authentication app (such as Authy, Google Authentication) to scan this QR code.'
				]
			);
			if (!$user->hasData('mp_tfa_enable')) {
				$user->setMpTfaEnable(1);
			}

			$mpTfaFieldset->addField('mp_tfa_secret_temp', '\Mageplaza\TwoFactorAuth\Block\Adminhtml\User\Edit\Tab\Renderer\QrCode', [
				'name' => 'mp_tfa_secret_temp'
			]);
			$mpTfaFieldset->addField('mp_tfa_secret_temp_hidden', 'hidden', ['name' => 'mp_tfa_secret_temp_hidden']);
			$mpTfaFieldset->addField('mp_tfa_secret', 'hidden', ['name' => 'mp_tfa_secret']);
			$mpTfaFieldset->addField('mp_tfa_status', 'hidden', ['name' => 'mp_tfa_status']);
			$mpTfaFieldset->addField('mp_tfa_one_code', 'text', [
				'name'  => 'mp_tfa_one_code',
				'label' => __('Confirmation Code'),
				'title' => __('Confirmation Code'),
				'note'  => 'Use the code provided by your authentication app.'
			]);
			$mpTfaFieldset->addField('mp_tfa_register', '\Mageplaza\TwoFactorAuth\Block\Adminhtml\User\Edit\Tab\Renderer\RegisterButton', [
				'name' => 'mp_tfa_register'
			]);


			$mpTfaFieldset->addField('mp_tfa_disable', '\Mageplaza\TwoFactorAuth\Block\Adminhtml\User\Edit\Tab\Renderer\DisableButton', [
				'name' => 'mp_tfa_disable'
			]);
			$mpTfaChildFieldset = $mpTfaFieldset->addFieldset('mp_tfa_trust_device', ['legend' => __('Trusted Devices')]);
			$mpTfaChildFieldset->addField('mp_tfa_trusted_device', 'label', [
				'name' => 'mp_tfa_trusted_device',
			])->setAfterElementHtml($this->getTrustedDeviceHtml($user));
			$data                       = $user->getData();
			$data['mp_tfa_secret_temp'] = $data['mp_tfa_secret_temp_hidden'] = $secret;
			$data['mp_tfa_status']      = $user->getMpTfaStatus();

			$form->setValues($data);
			$subject->setForm($form);
		}

		return $proceed();
	}

	/**
	 * @param $model
	 *
	 * @return mixed
	 */
	public function getTrustedDeviceHtml($model)
	{
		return $this->_layout
			->createBlock('\Mageplaza\TwoFactorAuth\Block\Adminhtml\User\Edit\Tab\Renderer\TrustedDevices')
			->setUserObject($model)
			->toHtml();
	}
}