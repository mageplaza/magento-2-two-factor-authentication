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
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
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

    protected function setUp()
    {
        $this->urlInterfaceMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])->getMock();

        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->storageSessionMock = $this->getMockBuilder(SessionManager::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->managerInterfaceMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->helperDataMock = $this->getMockBuilder(HelperData::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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
        $userMpTfaStatus = 0;

        /** @var Observer|PHPUnit_Framework_MockObject_MockObject $eventObserverMock */
        $eventObserverMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var Event|PHPUnit_Framework_MockObject_MockObject */
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getControllerAction', 'getRequest'])
            ->getMock();

        $userObjMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMpTfaStatus'])
            ->getMock();

        $responseObjMock = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();

        $userObjMock->expects($this->any())->method('getMpTfaStatus')->willReturn($userMpTfaStatus);
        $this->helperDataMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->helperDataMock->expects($this->once())->method('getForceTfaConfig')->willReturn(true);
        $this->authSessionMock->expects($this->once())->method('getUser')->willReturn($userObjMock);

        $eventObserverMock->expects($this->atLeastOnce())->method('getEvent')->willReturn($eventMock);
        /** @var Action|PHPUnit_Framework_MockObject_MockObject $controllerMock */
        $controllerMock = $this->getMockBuilder(AbstractAction::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRedirect', 'getRequest', 'getResponse'])
            ->getMockForAbstractClass();

        $controllerMock->expects($this->any())->method('getResponse')->willReturn($responseObjMock);

        /** @var RequestInterface|PHPUnit_Framework_MockObject_MockObject $requestMock */
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFullActionName'])
            ->getMockForAbstractClass();
        $requestMock->expects($this->any())->method('getFullActionName')->willReturn('not_in_array');
        $eventMock->expects($this->once())->method('getControllerAction')->willReturn($controllerMock);
        $eventMock->expects($this->once())->method('getRequest')->willReturn($requestMock);

        $this->model->execute($eventObserverMock);
    }
}
