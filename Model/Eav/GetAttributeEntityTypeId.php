<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Eav;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @inheritDoc
 */
class GetAttributeEntityTypeId implements GetAttributeEntityTypeIdInterface
{
    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    /**
     * @var array
     */
    private array $entityTypeId = [];

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * @inheritDoc
     */
    public function execute(string $entityTypeCode): int
    {
        if (!isset($this->entityTypeId[$entityTypeCode])) {
            $select = $this->connection->select()
                ->from($this->connection->getTableName('eav_entity_type'), 'entity_type_id')
                ->where('entity_type_code = ?', $entityTypeCode);
            $this->entityTypeId[$entityTypeCode] = (int) $this->connection->fetchOne($select);
        }
        return $this->entityTypeId[$entityTypeCode];
    }
}
