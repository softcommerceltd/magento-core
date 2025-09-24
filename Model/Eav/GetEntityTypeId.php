<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Eav;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @inheritDoc
 */
class GetEntityTypeId implements GetEntityTypeIdInterface
{
    /**
     * @var array
     */
    private array $entityTypeId = [];

    /**
     * @var AdapterInterface|null
     */
    private ?AdapterInterface $connection = null;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {}

    /**
     * @inheritDoc
     */
    public function execute(string $entityTypeCode = Product::ENTITY): int
    {
        if (!isset($this->entityTypeId[$entityTypeCode])) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from($connection->getTableName('eav_entity_type'), ['entity_type_id'])
                ->where('entity_type_code = ?', $entityTypeCode);
            $this->entityTypeId[$entityTypeCode] = (int) $connection->fetchOne($select);
        }
        return $this->entityTypeId[$entityTypeCode];
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resourceConnection->getConnection();
        }
        return $this->connection;
    }
}
