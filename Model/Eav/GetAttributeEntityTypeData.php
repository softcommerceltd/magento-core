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
class GetAttributeEntityTypeData implements GetAttributeEntityTypeDataInterface
{
    /**
     * @var array
     */
    private array $dataInMemory = [];

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(string $entityTypeCode, int|string|null $index = null): array|string|null
    {
        if (!isset($this->dataInMemory[$entityTypeCode])) {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from(
                    $connection->getTableName('eav_entity_type'),
                    [
                        'entity_type_id',
                        'entity_type_code',
                        'entity_model',
                        'attribute_model',
                        'entity_table',
                        'value_table_prefix',
                        'entity_id_field',
                        'default_attribute_set_id',
                        'additional_attribute_table'
                    ]
                )
                ->where('entity_type_code = ?', $entityTypeCode);

            $this->dataInMemory[$entityTypeCode] = $connection->fetchRow($select);
        }

        return null !== $index
            ? ($this->dataInMemory[$entityTypeCode][$index] ?? null)
            : $this->dataInMemory[$entityTypeCode];
    }
}
