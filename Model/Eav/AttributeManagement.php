<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Eav;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Swatches\Model\Swatch;
use Magento\Swatches\Model\SwatchAttributeType;
use SoftCommerce\Core\Framework\Filter\ParseStringInterface;
use SoftCommerce\Core\Model\Trait\ConnectionTrait;
use function array_filter;
use function array_flip;
use function array_keys;
use function current;
use function get_parent_class;
use function in_array;
use function is_array;
use function is_numeric;
use function is_string;
use function trim;

/**
 * @inheritDoc
 */
class AttributeManagement implements AttributeManagementInterface
{
    use ConnectionTrait;

    /**
     * @var array|Attribute|Attribute[]
     */
    private array $attributeEntity = [];

    /**
     * @var array
     */
    private array $attributeData = [];

    /**
     * @var array
     */
    private array $attributeCodeToId = [];

    /**
     * @var array
     */
    private array $attributeIdToAttributeSetData = [];

    /**
     * @var int[]
     */
    private array $attributeOptionSortOrderData = [];

    /**
     * @var array
     */
    private array $attributeSortOrderData = [];

    /**
     * @var array
     */
    private array $attributeSetAttributeIdData = [];

    /**
     * @var array
     */
    private array $attributeSetIdToNameData = [];

    /**
     * @var array
     */
    private array $defaultAttributeSetGroupId = [];

    /**
     * @var string
     */
    private string $entityTypeCode;

    /**
     * @var string
     */
    private string $valueIndex;

    /**
     * @var string[]
     */
    private array $forcedVisibleAttributes = [
        'image_label',
        'small_image_label',
        'thumbnail_label'
    ];

    /**
     * @var array|null
     */
    private ?array $entityTableMetadata = null;

    /**
     * @param AttributeRepositoryInterface $eavAttributeRepository
     * @param GetAttributeEntityTypeDataInterface $getAttributeEntityTypeData
     * @param ParseStringInterface $parseString
     * @param ResourceConnection $resourceConnection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SwatchAttributeType $swatchTypeChecker
     * @param string $entityTypeCode
     * @param string $valueIndex
     * @param array $forcedVisibleAttributes
     * @throws LocalizedException
     */
    public function __construct(
        private readonly AttributeRepositoryInterface $eavAttributeRepository,
        private readonly GetAttributeEntityTypeDataInterface $getAttributeEntityTypeData,
        private readonly ParseStringInterface $parseString,
        private readonly ResourceConnection $resourceConnection,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly SwatchAttributeType $swatchTypeChecker,
        string $entityTypeCode = ProductAttributeInterface::ENTITY_TYPE_CODE,
        string $valueIndex = self::OPTION_INDEX_TYPE_VALUE,
        array $forcedVisibleAttributes = []
    ) {
        $this->entityTypeCode = $entityTypeCode;
        $this->valueIndex = $valueIndex;
        $this->forcedVisibleAttributes = array_merge($this->forcedVisibleAttributes, $forcedVisibleAttributes);
        $this->initAttributes();
    }

    /**
     * @inheritDoc
     */
    public function getEntityTypeCode(): string
    {
        return $this->entityTypeCode;
    }

    /**
     * @inheritDoc
     */
    public function setEntityTypeCode(string $entityTypeCode): void
    {
        $this->entityTypeCode = $entityTypeCode;
    }

    /**
     * @inheritDoc
     */
    public function getEntityTypeId(): int
    {
        return (int) $this->getAttributeEntityTypeData->execute($this->getEntityTypeCode(), 'entity_type_id');
    }

    /**
     * @inheritDoc
     */
    public function getEntityTypeTable(): string
    {
        return (string) $this->getAttributeEntityTypeData->execute($this->getEntityTypeCode(), 'entity_table');
    }

    /**
     * @inheritDoc
     */
    public function getEntityTypeAdditionalTable(): string
    {
        return (string) $this->getAttributeEntityTypeData->execute($this->getEntityTypeCode(), 'additional_attribute_table');
    }

    /**
     * @inheritDoc
     */
    public function getAttributeSetIds(): array
    {
        return array_keys($this->attributeSetIdToNameData ?: []);
    }

    /**
     * @inheritDoc
     */
    public function getAttributeSetIdToNameMapping(?int $attributeSetId = null): array|string|null
    {
        return null !== $attributeSetId
            ? ($this->attributeSetIdToNameData[$attributeSetId] ?? null)
            : $this->attributeSetIdToNameData;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeSetToAttributeMapping(?int $attributeSetId = null): array
    {
        return null !== $attributeSetId
            ? ($this->attributeSetAttributeIdData[$attributeSetId] ?? [])
            : $this->attributeSetAttributeIdData;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeIds(): array
    {
        return array_keys($this->attributeIdToAttributeSetData);
    }

    /**
     * @inheritDoc
     */
    public function getAttributeIdsByAttributeSet(int $attributeSetId): array
    {
        return $this->getAttributeSetToAttributeMapping($attributeSetId);
    }

    /**
     * @inheritDoc
     */
    public function getAttributeIdByAttributeSet(int $attributeSetId, ?int $attributeId = null): array|int|null
    {
        $attributeIds = $this->getAttributeSetToAttributeMapping($attributeSetId);
        return null !== $attributeId
            ? ($attributeIds[$attributeId] ?? null)
            : $attributeIds;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeByCode(string $attributeCode): Attribute|ProductAttributeInterface|CategoryAttributeInterface
    {
        $attributeCode = $this->parseAttributeCode($attributeCode);
        if (!isset($this->attributeEntity[$attributeCode])) {
            $this->attributeEntity[$attributeCode] = $this->eavAttributeRepository->get(
                $this->getEntityTypeCode(),
                $attributeCode
            );
        }

        return $this->attributeEntity[$attributeCode];
    }

    /**
     * @inheritDoc
     */
    public function getAttributeData(?int $attributeId = null, int|string|null $index = null): mixed
    {
        if (null === $attributeId) {
            return $this->attributeData;
        }

        return null !== $index
            ? ($this->attributeData[$attributeId][$index] ?? null)
            : $this->attributeData[$attributeId] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getAttributeDataByIdOrCode(int|string $attributeIdOrCode, int|string|null $index = null): mixed
    {
        if (is_int($attributeIdOrCode)) {
            return $this->getAttributeData($attributeIdOrCode, $index);
        }
        return $this->getAttributeDataByCode($attributeIdOrCode, $index);
    }

    /**
     * @inheritDoc
     */
    public function setAttributeData(int $attributeId, array|int|string $attributeData, int|string|null $index = null): void
    {
        if (null !== $index) {
            $this->attributeData[$attributeId][$index] = $attributeData;
        } else {
            $this->attributeData[$attributeId] = $attributeData;
        }
    }

    /**
     * @inheritDoc
     */
    public function getAttributeDataByAttributeSet(int $attributeSetId, ?int $attributeId = null): array
    {
        $attributeIds = $this->getAttributeIdByAttributeSet($attributeSetId, $attributeId);

        // When requesting specific attribute: if it IS in the set, return it; if NOT, return empty array
        if ($attributeId !== null) {
            return $attributeIds ? $this->getAttributeData($attributeId) : [];
        }

        // When requesting all attributes in set
        $result = [];
        foreach ($attributeIds as $id) {
            if ($attribute = $this->getAttributeData($id)) {
                $result[$id] = $attribute;
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeCodeToIdMapping(?string $attributeCode = null): array|int|null
    {
        return null !== $attributeCode
            ? ($this->attributeCodeToId[$attributeCode] ?? null)
            : $this->attributeCodeToId;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeToIdToCodeMapping(?int $attributeId = null): array|string|null
    {
        $attributeMapping = array_flip($this->attributeCodeToId);
        return null !== $attributeId
            ? ($attributeMapping[$attributeId] ?? null)
            : $attributeMapping;
    }

    /**
     * @param AbstractAttribute $attribute
     * @return string|null
     */
    public function getAttributeType(AbstractAttribute $attribute): ?string
    {
        $frontendInput = $attribute->getFrontendInput();
        if ($attribute->usesSource() && in_array($frontendInput, ['select', 'multiselect', 'boolean'])) {
            return $frontendInput;
        }

        if ($attribute->isStatic()) {
            return $frontendInput == 'date' ? 'datetime' : 'varchar';
        }

        return $attribute->getBackendType();
    }

    /**
     * @inheritDoc
     */
    public function getDefaultAttributeSetGroupId(int $attributeSetId): ?int
    {
        if (!isset($this->defaultAttributeSetGroupId[$attributeSetId])) {
            $select = $this->getConnection()->select()
                ->from(
                    $this->getConnection()->getTableName('eav_attribute_group'),
                    'attribute_group_id'
                )
                ->where('attribute_set_id = ?', $attributeSetId)
                ->where('default_id = 1')
                ->limit(1);
            $this->defaultAttributeSetGroupId[$attributeSetId] = (int) $this->getConnection()->fetchOne($select);
        }

        return $this->defaultAttributeSetGroupId[$attributeSetId] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeDataByCode(?string $attributeCode = null, int|string|null $index = null): array|string|int|null
    {
        if ($attributeId = $this->getAttributeCodeToIdMapping($attributeCode)) {
            $result = $this->getAttributeData($attributeId);
        } else {
            $result = current(
                array_filter($this->getAttributeData(), function ($item) use ($attributeCode) {
                    return isset($item['attribute_code']) && $attributeCode === $item['attribute_code'];
                })
            ) ?: [];
        }

        return null !== $index
            ? ($result[$index] ?? null)
            : $result;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeDataByType(?string $backendType = null, ?int $attributeId = null): array
    {
        $result = array_filter($this->attributeData, function ($item) use ($backendType) {
            return isset($item['backend_type']) && $backendType === $item['backend_type'];
        });

        return null !== $attributeId
            ? ($result[$attributeId] ?? [])
            : $result;
    }

    /**
     * @inheritDoc
     */
    public function getRequiredAttributes(int $attributeSetId, array $excludeAttributes = []): array
    {
        $attributes = $this->getAttributeDataByAttributeSet($attributeSetId);
        return array_filter($attributes, function ($item) use ($excludeAttributes) {
            return isset($item['attribute_code'], $item['is_required'], $item['is_static'])
                && !in_array($item['attribute_code'], $excludeAttributes)
                && !$item['is_static']
                && $item['is_required'];
        });
    }

    /**
     * @inheritDoc
     */
    public function getAttributeDefaultValue(int|string $attribute): ?string
    {
        $attribute = $this->getAttributeDataByIdOrCode($attribute);
        return $attribute['default_value'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeOptions(int $attributeId, int|string|null $index = null): array|int|string|null
    {
        return null !== $index
            ? ($this->attributeData[$attributeId]['options'][$index] ?? null)
            : $this->attributeData[$attributeId]['options'] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function setAttributeOptions(int $attributeId, int $optionId, int|string $label): void
    {
        $index = $optionId;
        if ($this->valueIndex === self::OPTION_INDEX_TYPE_LABEL) {
            $index = $this->parseIndexValue($label);
            $label = $optionId;
        }

        $this->attributeData[$attributeId]['options'][$index] = $label;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeSortOrder(int $attributeSetId, int $attributeGroupId): int
    {
        if (!isset($this->attributeSortOrderData[$attributeSetId][$attributeGroupId])) {
            $select = $this->getConnection()->select()
                ->from(
                    $this->getConnection()->getTableName('eav_entity_attribute'),
                    new \Zend_Db_Expr("MAX(sort_order)")
                )
                ->where('attribute_set_id = ?', $attributeSetId)
                ->where('attribute_group_id = ?', $attributeGroupId);

            $this->attributeSortOrderData[$attributeSetId][$attributeGroupId] = (int) $this->getConnection()->fetchOne(
                $select
            );
        }

        return $this->attributeSortOrderData[$attributeSetId][$attributeGroupId];
    }

    /**
     * @inheritDoc
     */
    public function getAttributeOptionSortOrder(int $attributeId): int
    {
        if (!isset($this->attributeOptionSortOrderData[$attributeId])) {
            $select = $this->getConnection()->select()
                ->from($this->getConnection()->getTableName('eav_attribute_option'), 'sort_order')
                ->where('attribute_id = ?', $attributeId)
                ->order('sort_order ' . Select::SQL_DESC);

            $this->attributeOptionSortOrderData[$attributeId] = (int) $this->getConnection()->fetchOne($select);
        }

        return $this->attributeOptionSortOrderData[$attributeId];
    }

    /**
     * @inheritDoc
     */
    public function parseAttributeCode(string $attributeCode): string
    {
        return $this->parseString->execute($attributeCode);
    }

    /**
     * @inheritDoc
     */
    public function parseIndexValue(string|int|null $value): string|int|null
    {
        return is_string($value)
            ? $this->parseString->execute($value)
            : $value;
    }

    /**
     * @inheritDoc
     */
    public function createAttributeOptionValue(
        int $attributeId,
        array|string $optionValue,
        int $storeId = 0
    ): array {
        $attribute = $this->getAttributeData($attributeId);
        if (!$attribute) {
            throw new LocalizedException(
                __('Attribute with ID %1 does not exist.', $attributeId)
            );
        }

        if (!$this->canCreateAttributeOptionValue($attribute)) {
            return [];
        }

        $isSwatchAttribute = isset($attribute['is_swatch']) && $attribute['is_swatch'];
        $swatchType = $isSwatchAttribute && isset($attribute['is_swatch_text'])
            ? Swatch::SWATCH_TYPE_TEXTUAL
            : Swatch::SWATCH_TYPE_VISUAL_COLOR;
        $optionTable = $this->getConnection()->getTableName('eav_attribute_option');
        $optionValueTable = $this->getConnection()->getTableName('eav_attribute_option_value');
        $optionSwatchTable = $this->getConnection()->getTableName('eav_attribute_option_swatch');

        $result = [];
        $sortOrder = $this->getAttributeOptionSortOrder($attributeId);

        foreach (is_array($optionValue) ? $optionValue : [$optionValue] as $value) {
            $value = trim((string) $value);
            if (empty($value)) {
                continue;
            }

            // Check if option already exists to prevent duplicates
            if ($this->isAttributeOptionExist($attributeId, $value)) {
                continue;
            }

            $sortOrder++;

            // Start transaction for atomic operation
            $this->getConnection()->beginTransaction();

            try {
                // Insert into eav_attribute_option
                $insertOption = $this->getConnection()->insert(
                    $optionTable,
                    [
                        'attribute_id' => $attributeId,
                        'sort_order' => $sortOrder
                    ]
                );

                if (!$insertOption || !$optionId = $this->getConnection()->lastInsertId($optionTable)) {
                    throw new LocalizedException(__('Could not create attribute option.'));
                }

                // Insert into eav_attribute_option_value
                $insertValue = $this->getConnection()->insert(
                    $optionValueTable,
                    [
                        'option_id' => $optionId,
                        'store_id' => $storeId,
                        'value' => $value,
                    ]
                );

                if (!$insertValue) {
                    throw new LocalizedException(__('Could not create attribute option value.'));
                }

                // Insert into swatch table if applicable
                if ($isSwatchAttribute) {
                    $insertSwatch = $this->getConnection()->insert(
                        $optionSwatchTable,
                        [
                            'option_id' => $optionId,
                            'store_id' => $storeId,
                            'type' => $swatchType,
                            'value' => $value,
                        ]
                    );

                    if (!$insertSwatch) {
                        throw new LocalizedException(__('Could not create swatch option.'));
                    }
                }

                // Commit transaction
                $this->getConnection()->commit();

                // Add to result array - return simple indexed array of option IDs
                $result[] = (int) $optionId;

                // Update internal cache
                $this->setAttributeOptions($attributeId, (int) $optionId, $value);

            } catch (\Exception $e) {
                // Rollback on any failure
                $this->getConnection()->rollBack();
                throw $e;
            }
        }

        // Update cached sort order
        if (!empty($result)) {
            $this->attributeOptionSortOrderData[$attributeId] = $sortOrder;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function addAttributeToAttributeSet(int $attributeSetId, int $attributeId): void
    {
        $attribute = $this->getAttributeData($attributeId);
        $attributeCode = $attribute['attribute_code'] ?? null;
        if (!$attributeCode || !$attributeGroupId = $this->getDefaultAttributeSetGroupId($attributeSetId)) {
            return;
        }

        $sortOrder = $this->getAttributeSortOrder($attributeSetId, $attributeGroupId) + 1;
        $result = $this->getConnection()->insertOnDuplicate(
            $this->getConnection()->getTableName('eav_entity_attribute'),
            [
                'entity_type_id' => $this->getEntityTypeId(),
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'attribute_id' => $attributeId,
                'sort_order' => $sortOrder,
            ]
        );

        if (!$result) {
            return;
        }

        $this->attributeSetAttributeIdData[$attributeSetId][$attributeId] = $attributeId;
        $this->attributeIdToAttributeSetData[$attributeId][$attributeSetId] = $attributeSetId;
        $this->attributeSortOrderData[$attributeSetId][$attributeGroupId] = $sortOrder;
    }

    /**
     * @inheritDoc
     */
    public function isAttributeAssignedToSet(int $attributeSetId, int $attributeId): bool
    {
        return (bool) $this->getAttributeIdByAttributeSet($attributeSetId, $attributeId);
    }

    /**
     * @inheritDoc
     */
    public function isAttributeApplicableForEntityType(int|string $attribute, string $entityTypeId): bool
    {
        $attribute = $this->getAttributeDataByIdOrCode($attribute);
        return empty($attribute['apply_to']) || in_array($entityTypeId, $attribute['apply_to']);
    }

    /**
     * @inheritDoc
     */
    public function isAttributeRequiredForEntityType(int|string $attribute, string $entityTypeId): bool
    {
        $attribute = $this->getAttributeDataByIdOrCode($attribute);
        return isset($attribute['is_required'], $attribute['is_static'])
            && !$attribute['is_static']
            && $attribute['is_required']
            && (empty($attribute['apply_to']) || in_array($entityTypeId, $attribute['apply_to']));
    }

    /**
     * @inheritDoc
     */
    public function isAttributeSelect(int|string $attribute): bool
    {
        $attribute = $this->getAttributeDataByIdOrCode($attribute);
        return in_array($attribute['type'] ?? '', ['select', 'boolean']);
    }

    /**
     * @inheritDoc
     */
    public function isAttributeMultiselect(int|string $attribute): bool
    {
        $attribute = $this->getAttributeDataByIdOrCode($attribute);
        return ($attribute['type'] ?? '') === 'multiselect';
    }

    /**
     * @inheritDoc
     */
    public function isAttributeSelection(int|string $attribute): bool
    {
        $attribute = $this->getAttributeDataByIdOrCode($attribute);
        return in_array($attribute['type'] ?? '', ['select', 'multiselect', 'boolean']);
    }

    /**
     * @inheritDoc
     */
    public function isAttributeOptionExist(int|string $attribute, mixed $optionValue): bool
    {
        $attributeData = $this->getAttributeDataByIdOrCode($attribute);
        $options = $attributeData['options'] ?? [];
        return isset($options[$optionValue]);
    }

    /**
     * @inheritDoc
     */
    public function isAttributeStatic(int|string $attribute): bool
    {
        // First check if it's a column in the entity table (like sku, entity_id, type_id, etc.)
        // These are true static attributes stored directly in the main entity table
        if (in_array($attribute, $this->getEntityTableMetadata())) {
            return true;
        }

        // Then check the EAV is_static flag for attributes stored in static EAV tables
        $attribute = $this->getAttributeDataByIdOrCode($attribute);
        return isset($attribute['is_static']) && $attribute['is_static'];
    }

    /**
     * @inheritDoc
     */
    public function hasAttributeStaticOptions(array $attribute): bool
    {
        return isset($attribute['is_uses_source'], $attribute['source_model'], $attribute['options'])
            && $attribute['is_uses_source']
            && (
                $attribute['source_model'] !== Table::class
                || get_parent_class($attribute['source_model']) !== Table::class
            );
    }

    /**
     * @inheritDoc
     */
    public function hasAttributeStaticSourceModel(int|string $attribute): bool
    {
        $attribute = $this->getAttributeDataByIdOrCode($attribute);
        return isset($attribute['source_model']) && $attribute['source_model'] !== Table::class;
    }

    /**
     * @inheritDoc
     */
    public function canCreateAttributeOptionValue(array $attribute): bool
    {
        return isset($attribute['is_uses_source'], $attribute['options'])
            && $attribute['is_uses_source']
            && (
                !isset($attribute['source_model'])
                || $attribute['source_model'] === Table::class
                || get_parent_class($attribute['source_model']) === Table::class
            );
    }

    /**
     * Retrieve attribute options as key-value array
     *
     * Returns attribute options indexed by option value or label, depending on the
     * value index configuration ($this->valueIndex).
     *
     * Array structure depends on index type:
     * - OPTION_INDEX_TYPE_VALUE (default): [option_id => option_label]
     *   Example: [1 => 'Not Visible Individually', 2 => 'Catalog', 3 => 'Search', 4 => 'Catalog, Search']
     *
     * - OPTION_INDEX_TYPE_LABEL: [option_label => option_id]
     *   Example: ['Not Visible Individually' => 1, 'Catalog' => 2, 'Search' => 3, 'Catalog, Search' => 4]
     *
     * @param Attribute|AttributeInterface $attribute Attribute to retrieve options from
     * @return array Array of options indexed by value or label (key => label or label => value)
     */
    private function retrieveAttributeOptions(Attribute $attribute): array
    {
        if (!$attribute->usesSource()) {
            return [];
        }

        $attribute->setStoreId(0);

        try {
            $options = $attribute->getSource()->getAllOptions();
        } catch (\Exception $e) {
            $options = [];
        }

        $index = $this->valueIndex;
        $result = [];

        // Determine if this attribute uses a custom source model
        // Custom source models (e.g., country codes, language codes) often use case-sensitive
        // identifiers that follow international standards (ISO 3166-1, ISO 639-1).
        // We should preserve the original case for these to maintain data integrity.
        // Standard EAV source models use database-stored options where case normalization is safe.
        $sourceModel = $attribute->getSourceModel();
        $hasCustomSourceModel = $sourceModel && !str_contains($sourceModel, 'Eav\Entity\Attribute\Source');

        foreach ($options as $option) {
            $values = is_array($option['value']) ? $option['value'] : [$option];

            foreach ($values as $value) {
                if (!isset($value['value'])
                    || ($index !== self::OPTION_INDEX_TYPE_VALUE && !isset($value[$index]))
                ) {
                    continue;
                }

                $optionValue = is_numeric($value['value']) ? (int) $value['value'] : $value['value'];

                if ($index === self::OPTION_INDEX_TYPE_VALUE) {
                    $optionLabel = (string) ($value['label'] ?? '');
                } else {
                    $optionLabel = $optionValue;
                }

                $indexValue = $value[$index];
                if ($indexValue instanceof Phrase) {
                    $indexValue = $indexValue->render();
                }

                // Only normalize index value if NOT using a custom source model
                // This preserves case-sensitive identifiers like country codes (DE, IT, FR)
                // while maintaining case-insensitive matching for user-generated attribute options
                $normalizedIndex = $hasCustomSourceModel
                    ? $indexValue
                    : $this->parseIndexValue($indexValue);

                $result[$normalizedIndex] = $optionLabel;
            }
        }

        return $result;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function initAttributes(): void
    {
        $select = $this->getConnection()->select()
            ->from(
                ['eea' => $this->getConnection()->getTableName('eav_entity_attribute')],
                ['eea.attribute_id']
            )
            ->joinInner(
                ['eas' => $this->getConnection()->getTableName('eav_attribute_set')],
                'eas.attribute_set_id = eea.attribute_set_id',
                ['eas.attribute_set_id', 'eas.attribute_set_name']
            )
            ->where('eea.entity_type_id = ?', $this->getEntityTypeId());

        foreach ($this->getConnection()->fetchAll($select) as $item) {
            $attributeId = (int) ($item['attribute_id'] ?? null);
            if ($attributeId && $attributeSetId = (int) ($item['attribute_set_id'] ?? null)) {
                $this->attributeSetAttributeIdData[$attributeSetId][$attributeId] = $attributeId;
                $this->attributeIdToAttributeSetData[$attributeId][$attributeSetId] = $attributeSetId;
                $this->attributeSetIdToNameData[$attributeSetId] = $item['attribute_set_name'] ?? $attributeSetId;
            }
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(AttributeInterface::ATTRIBUTE_ID, $this->getAttributeIds(), 'in')
            ->create();

        $attributes = $this->eavAttributeRepository->getList(
            $this->getEntityTypeCode(),
            $searchCriteria
        );

        foreach ($attributes->getItems() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $attributeId = (int) $attribute->getAttributeId();
            if (!$attribute->getIsVisible() && !in_array($attributeCode, $this->forcedVisibleAttributes)) {
                continue;
            }

            $this->attributeEntity[$attributeCode] = $attribute;
            $this->attributeCodeToId[$attributeCode] = $attributeId;

            $this->setAttributeData(
                $attributeId,
                [
                    'attribute_id' => $attributeId,
                    'attribute_code' => $attributeCode,
                    'is_global' => (int) $attribute->getIsGlobal(),
                    'is_scope_global' => (bool) $attribute->isScopeGlobal(),
                    'is_scope_website' => (bool) $attribute->isScopeWebsite(),
                    'is_scope_store' => (bool) $attribute->isScopeStore(),
                    'is_required' => (bool) $attribute->getIsRequired(),
                    'is_unique' => (bool) $attribute->getIsUnique(),
                    'is_uses_source' => $attribute->usesSource(),
                    'is_static' => $attribute->isStatic(),
                    'is_swatch' => $this->swatchTypeChecker->isSwatchAttribute($attribute),
                    'is_swatch_text' => $this->swatchTypeChecker->isTextSwatch($attribute),
                    'is_swatch_visual' => $this->swatchTypeChecker->isVisualSwatch($attribute),
                    'backend_type' => $attribute->getBackendType(),
                    'backend_model' => $attribute->getBackendModel(),
                    'backend_table' => $attribute->getBackendTable(),
                    'frontend_input' => $attribute->getFrontendInput(),
                    'frontend_label' => $attribute->getFrontendLabel(),
                    'source_model' => $attribute->getSourceModel() ?: null,
                    'apply_to' => $attribute->getApplyTo(),
                    'type' => $this->getAttributeType($attribute),
                    'default_value' => $attribute->getDefaultValue(),
                    'options' => $this->retrieveAttributeOptions($attribute)
                ]
            );
        }
    }

    /**
     * Get entity table metadata (column names)
     *
     * Returns array of column names from the main entity table (e.g., catalog_product_entity).
     * These are true static attributes stored directly in the entity table rather than EAV tables.
     *
     * Common columns: entity_id, attribute_set_id, type_id, sku, has_options, required_options,
     * created_at, updated_at
     *
     * @return array Array of column names from entity table
     */
    private function getEntityTableMetadata(): array
    {
        if ($this->entityTableMetadata === null) {
            $entityTable = $this->getEntityTypeTable();
            $columns = $this->getConnection()->describeTable($entityTable);
            $this->entityTableMetadata = array_keys($columns);
        }

        return $this->entityTableMetadata;
    }
}
