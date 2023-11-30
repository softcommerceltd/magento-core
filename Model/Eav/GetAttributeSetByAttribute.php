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
class GetAttributeSetByAttribute implements GetAttributeSetByAttributeInterface
{
    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    /**
     * @var array|null
     */
    private ?array $data = null;

    /**
     * @var GetEntityTypeIdInterface
     */
    private GetEntityTypeIdInterface $getEntityTypeId;

    /**
     * @param GetEntityTypeIdInterface $getEntityTypeId
     * @param ResourceConnection $resource
     */
    public function __construct(
        GetEntityTypeIdInterface $getEntityTypeId,
        ResourceConnection $resource
    ) {
        $this->getEntityTypeId = $getEntityTypeId;
        $this->connection = $resource->getConnection();
    }

    /**
     * @inheritDoc
     */
    public function execute(int $attributeId): array
    {
        if (null === $this->data) {
            $this->data = $this->getData();
        }

        return $this->data[$attributeId] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function resetData(?int $attributeId = null): void
    {
        if (null !== $attributeId && isset($this->data[$attributeId])) {
            $this->data[$attributeId] = null;
        }
        $this->data = $this->getData($attributeId);
    }

    /**
     * @param int|null $attributeId
     * @return array
     */
    private function getData(?int $attributeId = null): array
    {
        $select = $this->connection->select()
            ->from(
                ['eea' => $this->connection->getTableName('eav_entity_attribute')],
                ['eea.attribute_id']
            )
            ->joinLeft(
                ['eas' => $this->connection->getTableName('eav_attribute_set')],
                'eas.attribute_set_id = eea.attribute_set_id',
                ['eas.attribute_set_id', 'eas.attribute_set_name']
            )
            ->where('eea.entity_type_id = ?', $this->getEntityTypeId->execute());

        if (null !== $attributeId) {
            $select->where('eea.attribute_id = ?', $attributeId);
        }

        $result = [];
        foreach ($this->connection->fetchAll($select) as $item) {
            if (isset($item['attribute_id'], $item['attribute_set_id'])) {
                $result[$item['attribute_id']][$item['attribute_set_id']] = $item;
            }
        }

        return $result;
    }
}
