<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Eav;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;

/**
 * @inheritDoc
 */
class GetAttributeBackendTable implements GetAttributeBackendTableInterface
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
    public function execute(int $attributeId, string $entityTypeCode): string
    {
        if (!isset($this->dataInMemory[$entityTypeCode])) {
            $this->dataInMemory[$entityTypeCode] = $this->getData($attributeId, $entityTypeCode);
        }
        return $this->dataInMemory[$entityTypeCode];
    }

    /**
     * @param int $attributeId
     * @param string $entityTypeCode
     * @return string
     * @throws LocalizedException
     */
    private function getData(int $attributeId, string $entityTypeCode): string
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('eav_entity_type'), 'entity_table')
            ->where('entity_type_code = ?', $entityTypeCode);

        if (!$entityTable = $connection->fetchOne($select)) {
            throw new LocalizedException(__('Entity "%1" dos\'nt exist', $entityTypeCode));
        }

        $select = $connection->select()
            ->from(
                $connection->getTableName('eav_attribute'),
                ['backend_type', 'backend_table']
            )
            ->where('attribute_id = ?', $attributeId);

        $attributeData = $connection->fetchRow($select);

        if (!isset($attributeData['backend_type'])
            || $attributeData['backend_type'] === AbstractAttribute::TYPE_STATIC
        ) {
            return $entityTable;
        }

        if (isset($attributeData['backend_table'])) {
            return $attributeData['backend_table'];
        }

        $entityTable .= "_{$attributeData['backend_type']}";

        if (!$connection->isTableExists(
            $connection->getTableName($entityTable)
        )) {
            throw new LocalizedException(
                __('Table with the name "%1" does\'nt exist.', $entityTable)
            );
        }

        return $entityTable;
    }
}
