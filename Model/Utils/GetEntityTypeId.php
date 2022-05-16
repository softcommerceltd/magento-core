<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Utils;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @inheritDoc
 */
class GetEntityTypeId implements GetEntityTypeIdInterface
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var int|null
     */
    private $entityTypeId;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * @param string $entityTypeCode
     * @return int
     */
    public function execute(string $entityTypeCode = Product::ENTITY): int
    {
        if (!isset($this->entityTypeId[$entityTypeCode])) {
            $select = $this->connection->select()
                ->from($this->connection->getTableName('eav_entity_type'), ['entity_type_id'])
                ->where('entity_type_code = ?', $entityTypeCode);
            $this->entityTypeId[$entityTypeCode] = (int) $this->connection->fetchOne($select);
        }
        return $this->entityTypeId[$entityTypeCode];
    }
}
