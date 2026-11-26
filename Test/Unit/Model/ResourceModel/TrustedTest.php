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

namespace Mageplaza\TwoFactorAuth\Test\Unit\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Mageplaza\TwoFactorAuth\Model\ResourceModel\Trusted;
use PHPUnit\Framework\TestCase;

class TrustedTest extends TestCase
{
    private function resource(array $rows): Trusted
    {
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('select')->willReturn($select);
        $adapter->method('fetchAll')->willReturn($rows);

        return new class($adapter) extends Trusted {
            private $adapter;

            public function __construct($adapter)
            {
                $this->adapter = $adapter;
            }

            public function getConnection()
            {
                return $this->adapter;
            }

            public function getMainTable()
            {
                return 'mageplaza_twofactorauth_trusted';
            }
        };
    }

    public function testReturnsIdOfRowWithMatchingHash(): void
    {
        $resource = $this->resource([
            ['trusted_id' => '1', 'token_hash' => hash('sha256', 'a')],
            ['trusted_id' => '2', 'token_hash' => hash('sha256', 'b')],
        ]);

        $this->assertSame('2', $resource->getTrustedIdByToken(7, hash('sha256', 'b')));
    }

    public function testReturnsFalseWhenNoRowMatches(): void
    {
        $resource = $this->resource([['trusted_id' => '1', 'token_hash' => hash('sha256', 'a')]]);

        $this->assertFalse($resource->getTrustedIdByToken(7, hash('sha256', 'b')));
    }

    public function testReturnsFalseWhenUserHasNoTokenRows(): void
    {
        $this->assertFalse($this->resource([])->getTrustedIdByToken(7, hash('sha256', 'b')));
    }
}
