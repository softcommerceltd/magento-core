<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Eav;

use Magento\Framework\App\ResourceConnection;
use SoftCommerce\Core\Model\Trait\ConnectionTrait;

/**
 * @inheritDoc
 */
class GetAttributeSetByAttribute implements GetAttributeSetByAttributeInterface
{
    use ConnectionTrait;

    /**
     * @var array|null
     */
    private ?array $data = null;

    /**
     * @param GetEntityTypeIdInterface $getEntityTypeId
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly GetEntityTypeIdInterface $getEntityTypeId,
        private readonly ResourceConnection $resourceConnection
    ) {
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
        $select = $this->getConnection()->select()
            ->from(
                ['eea' => $this->getConnection()->getTableName('eav_entity_attribute')],
                ['eea.attribute_id']
            )
            ->joinLeft(
                ['eas' => $this->getConnection()->getTableName('eav_attribute_set')],
                'eas.attribute_set_id = eea.attribute_set_id',
                ['eas.attribute_set_id', 'eas.attribute_set_name']
            )
            ->where('eea.entity_type_id = ?', $this->getEntityTypeId->execute());

        if (null !== $attributeId) {
            $select->where('eea.attribute_id = ?', $attributeId);
        }

        $result = [];
        foreach ($this->getConnection()->fetchAll($select) as $item) {
            if (isset($item['attribute_id'], $item['attribute_set_id'])) {
                $result[$item['attribute_id']][$item['attribute_set_id']] = $item;
            }
        }

        return $result;
    }
}
