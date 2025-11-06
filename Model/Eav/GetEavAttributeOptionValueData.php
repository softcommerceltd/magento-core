<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Eav;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Phrase;
use Magento\Store\Model\Store;
use SoftCommerce\Core\Model\Trait\ConnectionTrait;

/**
 * @inheritDoc
 */
class GetEavAttributeOptionValueData implements GetEavAttributeOptionValueDataInterface
{
    use ConnectionTrait;

    /**
     * @var array
     */
    private array $data = [];

    /**
     * @var array|null
     */
    private ?array $sourceModelInMemory = null;

    /**
     * @var AbstractSource[]|null
     */
    private ?array $sourceModelObjectInMemory = null;

    /**
     * @param GetEntityTypeIdInterface $getEntityTypeId
     * @param ObjectFactory $objectFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly GetEntityTypeIdInterface $getEntityTypeId,
        private readonly ObjectFactory $objectFactory,
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(int $attributeId): array
    {
        if (!isset($this->data[$attributeId])) {
            $this->data[$attributeId] = $this->getData($attributeId);
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
        $this->data = [];
    }

    /**
     * @param int $attributeId
     * @return array
     */
    private function getData(int $attributeId): array
    {
        if ($sourceModel = $this->getSourceModel($attributeId)) {
            return $this->getAttributeSourceOptions($attributeId, $sourceModel);
        }

        $select = $this->getConnection()->select()
            ->from($this->getConnection()->getTableName('eav_attribute_option'))
            ->where('attribute_id = ?', $attributeId);

        $result = [];
        foreach ($this->getConnection()->fetchAll($select) as $item) {
            if (!$optionId = (int) ($item['option_id'] ?? null)) {
                continue;
            }

            $selectValue = $this->getConnection()->select()
                ->from(
                    $this->getConnection()->getTableName('eav_attribute_option_value'),
                    [
                        'store_id',
                        'value'
                    ]
                )
                ->where('option_id = ?', $optionId);

            $values = [];
            foreach ($this->getConnection()->fetchAll($selectValue) as $value) {
                if (!isset($value['store_id'], $value['value'])) {
                    continue;
                }

                if ($value['store_id'] == 0) {
                    $item['value'] = $value['value'];
                    continue;
                }

                $values[$value['store_id']] = $value['value'];
            }

            $item['values'] = $values;
            $result[$optionId] = $item;
        }

        return $result;
    }

    /**
     * @param int $attributeId
     * @return string|null
     */
    private function getSourceModel(int $attributeId): ?string
    {
        if (null === $this->sourceModelInMemory) {
            $select = $this->getConnection()->select()
                ->from($this->getConnection()->getTableName('eav_attribute'), ['attribute_id', 'source_model'])
                ->where('entity_type_id = ?', $this->getEntityTypeId->execute())
                ->where('source_model IS NOT NULL')
                ->where('source_model != ?', Table::class);

            $this->sourceModelInMemory = $this->getConnection()->fetchPairs($select);
        }

        return $this->sourceModelInMemory[$attributeId] ?? null;
    }

    /**
     * @param int $attributeId
     * @param string $sourceModel
     * @return AbstractSource|mixed|null
     */
    private function getSourceModelObject(int $attributeId, string $sourceModel)
    {
        if (!isset($this->sourceModelObjectInMemory[$attributeId])) {
            $this->sourceModelObjectInMemory[$attributeId] = $this->objectFactory->get($sourceModel);
        }

        return $this->sourceModelObjectInMemory[$attributeId] ?? null;
    }

    /**
     * @param int $attributeId
     * @param string $sourceModel
     * @return array
     */
    private function getAttributeSourceOptions(int $attributeId, string $sourceModel): array
    {
        if (!$sourceModel = $this->getSourceModelObject($attributeId, $sourceModel)) {
            return [];
        }

        $result = [];
        $i = 0;
        foreach ($sourceModel->getAllOptions() as $option) {
            if (!$value = $option['label'] ?? ($option['value'] ?? null)) {
                continue;
            }

            if ($value instanceof Phrase) {
                $value = $value->render();
            }

            $result[] = [
                'option_id' => 0,
                'attribute_id' => $attributeId,
                'sort_order' => $i++,
                'value' => $value,
                'values' => [
                    Store::DEFAULT_STORE_ID => $value
                ]
            ];
        }

        return $result;
    }
}
