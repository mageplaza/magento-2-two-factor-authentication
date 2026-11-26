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

namespace Mageplaza\TwoFactorAuth\Model;

use DateInterval;
use DateTime;
use Magento\User\Model\Backend\Config\ObserverConfig;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;

class TwoFactorLockout
{
    /**
     * @var ObserverConfig
     */
    protected $observerConfig;

    /**
     * @var UserResource
     */
    protected $userResource;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * TwoFactorLockout constructor.
     *
     * @param ObserverConfig $observerConfig
     * @param UserResource $userResource
     * @param UserFactory $userFactory
     */
    public function __construct(
        ObserverConfig $observerConfig,
        UserResource $userResource,
        UserFactory $userFactory
    ) {
        $this->observerConfig = $observerConfig;
        $this->userResource   = $userResource;
        $this->userFactory    = $userFactory;
    }

    /**
     * @param User $pendingUser
     *
     * @return User
     */
    public function reload($pendingUser): User
    {
        $user = $this->userFactory->create();
        $this->userResource->load($user, $pendingUser->getId());

        return $user;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isLocked($user): bool
    {
        $lockExpires = $user->getLockExpires();

        return $lockExpires && new DateTime($lockExpires) > new DateTime();
    }

    /**
     * @param User $user
     *
     * @return bool true when the account has just been locked
     */
    public function recordFailure($user): bool
    {
        $lockThreshold = $this->observerConfig->getAdminLockThreshold();
        $maxFailures   = $this->observerConfig->getMaxFailures();
        if (!($lockThreshold && $maxFailures)) {
            return false;
        }

        $failures = (int) $user->getMpTfaFailures() + 1;
        if ($failures < $maxFailures) {
            $this->saveFailures($user, $failures);

            return false;
        }

        $lockExpires = (new DateTime())->add(new DateInterval('PT' . $lockThreshold . 'S'));
        $this->userResource->updateFailure($user, $lockExpires, false);
        $this->saveFailures($user, 0);

        return true;
    }

    /**
     * @param User $user
     */
    public function reset($user): void
    {
        $this->userResource->unlock($user->getId());
        $this->saveFailures($user, 0);
    }

    /**
     * @param User $user
     * @param int $failures
     */
    private function saveFailures($user, int $failures): void
    {
        $connection = $this->userResource->getConnection();
        $connection->update(
            $this->userResource->getMainTable(),
            ['mp_tfa_failures' => $failures],
            $connection->quoteInto('user_id = ?', $user->getId())
        );
    }
}
