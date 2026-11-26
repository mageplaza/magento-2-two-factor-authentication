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

declare(strict_types=1);

namespace Mageplaza\TwoFactorAuth\Test\Unit\Model;

use DateTime;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\User\Model\Backend\Config\ObserverConfig;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use Mageplaza\TwoFactorAuth\Model\TwoFactorLockout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TwoFactorLockoutTest extends TestCase
{
    /** @var ObserverConfig|MockObject */
    private $config;

    /** @var UserResource|MockObject */
    private $resource;

    /** @var AdapterInterface|MockObject */
    private $connection;

    /** @var UserFactory|MockObject */
    private $userFactory;

    /** @var TwoFactorLockout */
    private $lockout;

    protected function setUp(): void
    {
        $this->config      = $this->createMock(ObserverConfig::class);
        $this->resource    = $this->createMock(UserResource::class);
        $this->connection  = $this->createMock(AdapterInterface::class);
        $this->resource->method('getConnection')->willReturn($this->connection);
        $this->resource->method('getMainTable')->willReturn('admin_user');
        $this->connection->method('quoteInto')->willReturn('user_id = 7');
        $this->userFactory = $this->createMock(UserFactory::class);
        $this->config->method('getAdminLockThreshold')->willReturn(1800);
        $this->config->method('getMaxFailures')->willReturn(6);
        $this->lockout = new TwoFactorLockout($this->config, $this->resource, $this->userFactory);
    }

    private function user(array $data): User
    {
        $user = new class extends User {
            public function __construct()
            {
            }
        };
        $user->setData([
            'id'           => 7,
            'mp_tfa_failures' => $data['failures'] ?? 0,
            'lock_expires' => $data['lock'] ?? null,
        ]);

        return $user;
    }

    public function testReloadLoadsFreshUserFromDatabase(): void
    {
        $fresh = $this->createMock(User::class);
        $this->userFactory->method('create')->willReturn($fresh);
        $this->resource->expects($this->once())->method('load')->with($fresh, 7);

        $this->assertSame($fresh, $this->lockout->reload($this->user([])));
    }

    public function testIsLockedOnlyWhileLockExpiresIsInTheFuture(): void
    {
        $future = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');
        $past   = (new DateTime('-10 minutes'))->format('Y-m-d H:i:s');

        $this->assertTrue($this->lockout->isLocked($this->user(['lock' => $future])));
        $this->assertFalse($this->lockout->isLocked($this->user(['lock' => $past])));
        $this->assertFalse($this->lockout->isLocked($this->user([])));
    }

    public function testFailureIncrementsOwnCounterWithoutLocking(): void
    {
        $user = $this->user(['failures' => 2]);
        $this->resource->expects($this->never())->method('updateFailure');
        $this->connection->expects($this->once())
            ->method('update')
            ->with('admin_user', ['mp_tfa_failures' => 3], 'user_id = 7');

        $this->assertFalse($this->lockout->recordFailure($user));
    }

    public function testMaxFailuresLocksAccountAndResetsCounter(): void
    {
        $user = $this->user(['failures' => 5]);
        $this->resource->expects($this->once())
            ->method('updateFailure')
            ->with($user, $this->isInstanceOf(DateTime::class), false);
        $this->connection->expects($this->once())
            ->method('update')
            ->with('admin_user', ['mp_tfa_failures' => 0], 'user_id = 7');

        $this->assertTrue($this->lockout->recordFailure($user));
    }

    public function testCounterSurvivesPasswordReentry(): void
    {
        $stored  = 0;
        $locked  = 0;
        $this->connection->method('update')->willReturnCallback(
            function ($table, $bind) use (&$stored) {
                $stored = $bind['mp_tfa_failures'];

                return 1;
            }
        );
        $this->resource->method('updateFailure')->willReturnCallback(
            function () use (&$locked) {
                $locked++;
            }
        );
        $this->resource->expects($this->never())->method('unlock');

        $results = [];
        for ($i = 1; $i <= 6; $i++) {
            $results[] = $this->lockout->recordFailure($this->user(['failures' => $stored]));
        }

        $this->assertSame([false, false, false, false, false, true], $results);
        $this->assertSame(1, $locked);
        $this->assertSame(0, $stored);
    }

    public function testDisabledLockoutNeverCounts(): void
    {
        $config = $this->createMock(ObserverConfig::class);
        $config->method('getAdminLockThreshold')->willReturn(0);
        $config->method('getMaxFailures')->willReturn(0);
        $lockout = new TwoFactorLockout($config, $this->resource, $this->userFactory);
        $this->connection->expects($this->never())->method('update');

        $this->assertFalse($lockout->recordFailure($this->user([])));
    }

    public function testResetUnlocksUserAndClearsCounter(): void
    {
        $this->resource->expects($this->once())->method('unlock')->with(7);
        $this->connection->expects($this->once())
            ->method('update')
            ->with('admin_user', ['mp_tfa_failures' => 0], 'user_id = 7');

        $this->lockout->reset($this->user(['failures' => 3]));
    }
}
