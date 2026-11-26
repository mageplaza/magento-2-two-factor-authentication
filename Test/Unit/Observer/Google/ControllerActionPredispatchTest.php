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

namespace Mageplaza\TwoFactorAuth\Test\Unit\Observer\Google;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\User;
use Mageplaza\TwoFactorAuth\Helper\Data as HelperData;
use Mageplaza\TwoFactorAuth\Observer\Google\ControllerActionPredispatch;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Test class for Magento\User\Observer\Backend\ForceAdminPasswordChangeObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ControllerActionPredispatchTest extends TestCase
{
    /** @var UrlInterface|PHPUnit_Framework_MockObject_MockObject */
    protected $urlInterfaceMock;

    /** @var Session|PHPUnit_Framework_MockObject_MockObject */
    protected $authSessionMock;

    /** @var ActionFlag|PHPUnit_Framework_MockObject_MockObject */
    protected $actionFlagMock;

    /** @var SessionManager|PHPUnit_Framework_MockObject_MockObject */
    protected $storageSessionMock;

    /** @var ManagerInterface|PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterfaceMock;

    /** @var HelperData|PHPUnit_Framework_MockObject_MockObject */
    protected $helperDataMock;

    /** @var ControllerActionPredispatch */
    protected $model;

    protected function setUp(): void
    {
        $this->urlInterfaceMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__call'])->getMock();

        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storageSessionMock = $this->getMockBuilder(SessionManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->managerInterfaceMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperDataMock = $this->getMockBuilder(HelperData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper = new ObjectManager($this);

        $this->model = $helper->getObject(
            ControllerActionPredispatch::class,
            [
                'url'             => $this->urlInterfaceMock,
                'authSession'     => $this->authSessionMock,
                'actionFlag'      => $this->actionFlagMock,
                '_storageSession' => $this->storageSessionMock,
                '_messageManager' => $this->managerInterfaceMock,
                '_helperData'     => $this->helperDataMock,
            ]
        );
    }

    public function testForceTwoAuthFactorChange()
    {
        $userObjMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $userObjMock->setData('mp_tfa_status', 0);

        $responseObjMock = $this->getMockBuilder(HttpResponse::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setRedirect'])
            ->getMock();
        $responseObjMock->expects($this->once())->method('setRedirect');

        $this->helperDataMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->helperDataMock->expects($this->once())->method('getForceTfaConfig')->willReturn(true);
        $this->authSessionMock->expects($this->once())->method('__call')
            ->with('getUser')
            ->willReturn($userObjMock);

        $controllerMock = $this->createMock(AbstractAction::class);
        $controllerMock->expects($this->any())->method('getResponse')->willReturn($responseObjMock);

        $requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFullActionName'])
            ->getMock();
        $requestMock->expects($this->any())->method('getFullActionName')->willReturn('not_in_array');

        $eventObserver = new Observer([
            'event' => new Event(['controller_action' => $controllerMock, 'request' => $requestMock])
        ]);

        $this->model->execute($eventObserver);
    }
}
