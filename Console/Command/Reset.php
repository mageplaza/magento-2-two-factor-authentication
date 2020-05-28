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
 * @package     Mageplaza_AlsoBought
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\TwoFactorAuth\Console\Command;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Model\UserFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Reset
 * @package Mageplaza\TwoFactorAuth\Console\Command
 */
class Reset extends Command
{
    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * Reset constructor.
     *
     * @param UserFactory $userFactory
     * @param null $name
     */
    public function __construct(
        UserFactory $userFactory,
        $name = null
    ) {
        $this->userFactory = $userFactory;

        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument('user', InputArgument::REQUIRED, 'Admin User Name');

        $this->setName('mageplaza-2fa:reset')
            ->setDescription('Reset Mageplaza 2 Factor Authentication for Admin User');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userName = $input->getArgument('user');
        $user     = $this->userFactory->create()->loadByUsername($userName);

        try {
            if (!$user || !$user->getId()) {
                throw new LocalizedException(__('User "%1" does not exist', $userName));
            }
            $user->setMpTfaEnable(false)
                ->setMpTfaSecret(false)
                ->setMpTfaStatus(false)
                ->save();
            $output->writeln('<info>Successfully!</info>');
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}
