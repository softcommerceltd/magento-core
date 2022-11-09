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
class GetDefaultAttributeSetId implements GetDefaultAttributeSetIdInterface
{
    /**
     * @var array
     */
    private array $attributeSetId = [];

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

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
        if (!isset($this->attributeSetId[$entityTypeCode])) {
            $select = $this->connection->select()
                ->from($this->connection->getTableName('eav_entity_type'), 'default_attribute_set_id')
                ->where('entity_type_code = ?', $entityTypeCode);
            $this->attributeSetId[$entityTypeCode] = (int) $this->connection->fetchOne($select);
        }
        return $this->attributeSetId[$entityTypeCode];
    }
}
