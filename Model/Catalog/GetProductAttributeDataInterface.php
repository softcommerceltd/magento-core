<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;

/**
 * Interface GetProductAttributeDataInterface
 * used to obtain product attributes and their values.
 */
interface GetProductAttributeDataInterface
{
    /**
     * @param int $entityId
     * @param int $storeId
     * @param int|null $attributeSetId
     * @param bool $shouldIncludeStaticAttributes
     * @return $this
     * @throws LocalizedException
     */
    public function execute(
        int $entityId,
        int $storeId = Store::DEFAULT_STORE_ID,
        ?int $attributeSetId = null,
        bool $shouldIncludeStaticAttributes = false
    ): static;

    /**
     * @return array
     */
    public function getData(): array;

    /**
     * @param array|string $attributeCode
     * @return array|string|null
     */
    public function getDataByAttributeCode(array|string $attributeCode): array|string|null;

    /**
     * @param array|int $attributeId
     * @return array
     */
    public function getDataByAttributeId(array|int $attributeId): array;

    /**
     * @param int|null $entityId
     * @param int|null $storeId
     * @return void
     */
    public function resetData(?int $entityId = null, ?int $storeId = null): void;
}
