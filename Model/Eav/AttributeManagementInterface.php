<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Eav;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface AttributeManagementInterface
 * used to read/write EAV attribute data.
 */
interface AttributeManagementInterface
{
    public const EAV_ATTRIBUTE_TYPE = 'catalog_eav_attribute';
    public const OPTION_INDEX_TYPE_LABEL = 'label';
    public const OPTION_INDEX_TYPE_VALUE = 'value';

    /**
     * @return string
     */
    public function getEntityTypeCode(): string;

    /**
     * @param string $entityTypeCode
     * @return void
     */
    public function setEntityTypeCode(string $entityTypeCode): void;

    /**
     * @return int
     */
    public function getEntityTypeId(): int;

    /**
     * @return string
     */
    public function getEntityTypeTable(): string;

    /**
     * @return string
     */
    public function getEntityTypeAdditionalTable(): string;

    /**
     * @return array
     */
    public function getAttributeSetIds(): array;

    /**
     * @param int|null $attributeSetId
     * @return array|string|null
     */
    public function getAttributeSetIdToNameMapping(?int $attributeSetId = null): array|string|null;

    /**
     * @param int|null $attributeSetId
     * @return array
     */
    public function getAttributeSetToAttributeMapping(?int $attributeSetId = null): array;

    /**
     * @return array
     */
    public function getAttributeIds(): array;

    /**
     * @param int $attributeSetId
     * @return array
     */
    public function getAttributeIdsByAttributeSet(int $attributeSetId): array;

    /**
     * @param int $attributeSetId
     * @param int|null $attributeId
     * @return array|int|null
     */
    public function getAttributeIdByAttributeSet(int $attributeSetId, ?int $attributeId = null): array|int|null;

    /**
     * @param string $attributeCode
     * @return Attribute|ProductAttributeInterface|CategoryAttributeInterface
     * @throws NoSuchEntityException
     */
    public function getAttributeByCode(string $attributeCode): Attribute|ProductAttributeInterface|CategoryAttributeInterface;

    /**
     * @param int|null $attributeId
     * @param int|string|null $index
     * @return mixed
     */
    public function getAttributeData(?int $attributeId = null, int|string|null $index = null): mixed;

    /**
     * @param int|string $attributeIdOrCode
     * @param int|string|null $index
     * @return mixed
     */
    public function getAttributeDataByIdOrCode(int|string $attributeIdOrCode, int|string|null $index = null): mixed;

    /**
     * @param int $attributeId
     * @param array|int|string $attributeData
     * @param int|string|null $index
     * @return void
     */
    public function setAttributeData(int $attributeId, array|int|string $attributeData, int|string|null $index = null): void;

    /**
     * @param int $attributeSetId
     * @param int|null $attributeId
     * @return array
     */
    public function getAttributeDataByAttributeSet(int $attributeSetId, ?int $attributeId = null): array;

    /**
     * @param string|null $attributeCode
     * @return array|int|null
     */
    public function getAttributeCodeToIdMapping(?string $attributeCode = null): array|int|null;

    /**
     * @param int|null $attributeId
     * @return array|string|null
     */
    public function getAttributeToIdToCodeMapping(?int $attributeId = null): array|string|null;

    /**
     * @param AbstractAttribute $attribute
     * @return string|null
     */
    public function getAttributeType(AbstractAttribute $attribute): ?string;

    /**
     * @param int $attributeSetId
     * @return int|null
     */
    public function getDefaultAttributeSetGroupId(int $attributeSetId): ?int;

    /**
     * @param string|null $attributeCode
     * @param int|string|null $index
     * @return array|string|int|null
     */
    public function getAttributeDataByCode(?string $attributeCode = null, int|string|null $index = null): array|string|int|null;

    /**
     * @param string|null $backendType
     * @param int|null $attributeId
     * @return array
     */
    public function getAttributeDataByType(?string $backendType = null, ?int $attributeId = null): array;

    /**
     * @param int $attributeSetId
     * @param array $excludeAttributes
     * @return array
     */
    public function getRequiredAttributes(int $attributeSetId, array $excludeAttributes = []): array;

    /**
     * @param int|string $attribute
     * @return string|null
     */
    public function getAttributeDefaultValue(int|string $attribute): ?string;

    /**
     * @param int $attributeId
     * @param int|string|null $index
     * @return array|int|string|null
     */
    public function getAttributeOptions(int $attributeId, int|string|null $index = null): array|int|string|null;

    /**
     * @param int $attributeId
     * @param int $optionId
     * @param int|string $label
     * @return void
     */
    public function setAttributeOptions(int $attributeId, int $optionId, int|string $label): void;

    /**
     * @param int $attributeSetId
     * @param int $attributeGroupId
     * @return int
     */
    public function getAttributeSortOrder(int $attributeSetId, int $attributeGroupId): int;

    /**
     * @param int $attributeId
     * @return int
     */
    public function getAttributeOptionSortOrder(int $attributeId): int;

    /**
     * @param string $attributeCode
     * @return string
     */
    public function parseAttributeCode(string $attributeCode): string;

    /**
     * @param string|int|null $value
     * @return string|int|null
     */
    public function parseIndexValue(string|int|null $value): string|int|null;

    /**
     * @param int $attributeId
     * @param array|string $optionValue
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     */
    public function createAttributeOptionValue(
        int $attributeId,
        array|string $optionValue,
        int $storeId = 0
    ): array;

    /**
     * @param int $attributeSetId
     * @param int $attributeId
     * @return void
     */
    public function addAttributeToAttributeSet(int $attributeSetId, int $attributeId): void;

    /**
     * @param int $attributeSetId
     * @param int $attributeId
     * @return bool
     */
    public function isAttributeAssignedToSet(int $attributeSetId, int $attributeId): bool;

    /**
     * @param int|string $attribute
     * @param string $entityTypeId
     * @return bool
     */
    public function isAttributeApplicableForEntityType(int|string $attribute, string $entityTypeId): bool;

    /**
     * @param int|string $attribute
     * @param string $entityTypeId
     * @return bool
     */
    public function isAttributeRequiredForEntityType(int|string $attribute, string $entityTypeId): bool;

    /**
     * @param int|string $attribute
     * @return bool
     */
    public function isAttributeSelect(int|string $attribute): bool;

    /**
     * @param int|string $attribute
     * @return bool
     */
    public function isAttributeMultiselect(int|string $attribute): bool;

    /**
     * @param int|string $attribute
     * @return bool
     */
    public function isAttributeSelection(int|string $attribute): bool;

    /**
     * @param int|string $attribute
     * @param $optionValue
     * @return bool
     */
    public function isAttributeOptionExist(int|string $attribute, $optionValue): bool;

    /**
     * @param int|string $attribute
     * @return bool
     */
    public function isAttributeStatic(int|string $attribute): bool;

    /**
     * @param array $attribute
     * @return bool
     */
    public function hasAttributeStaticOptions(array $attribute): bool;

    /**
     * @param int|string $attribute
     * @return bool
     */
    public function hasAttributeStaticSourceModel(int|string $attribute): bool;

    /**
     * @param array $attribute
     * @return bool
     */
    public function canCreateAttributeOptionValue(array $attribute): bool;
}
