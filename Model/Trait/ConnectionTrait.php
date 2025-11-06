<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Trait;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Trait ConnectionTrait
 *
 * Provides lazy-loading database connection functionality.
 * This trait is used throughout all modules to avoid duplicating
 * connection initialization logic.
 *
 * Requirements:
 * - The using class MUST have a property: private ResourceConnection $resourceConnection
 * - This property should be injected via constructor using property promotion
 *
 * Usage example:
 * <code>
 * class MyClass
 * {
 *     use ConnectionTrait;
 *
 *     public function __construct(
 *         private ResourceConnection $resourceConnection
 *     ) {}
 *
 *     public function myMethod(): void
 *     {
 *         $connection = $this->getConnection();
 *         $tableName = $connection->getTableName('my_table');
 *         // ... use connection
 *     }
 * }
 * </code>
 */
trait ConnectionTrait
{
    /**
     * @var AdapterInterface|null
     */
    private ?AdapterInterface $connection = null;

    /**
     * Get database connection with lazy initialization
     *
     * @return AdapterInterface
     */
    protected function getConnection(): AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resourceConnection->getConnection();
        }
        return $this->connection;
    }
}
