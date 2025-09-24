<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Eav;

use Magento\Framework\App\ResourceConnection;

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
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {}

    /**
     * @inheritDoc
     */
    public function execute(string $entityTypeCode): int
    {
        if (!isset($this->attributeSetId[$entityTypeCode])) {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from($connection->getTableName('eav_entity_type'), 'default_attribute_set_id')
                ->where('entity_type_code = ?', $entityTypeCode);
            $this->attributeSetId[$entityTypeCode] = (int) $connection->fetchOne($select);
        }
        return $this->attributeSetId[$entityTypeCode];
    }
}
