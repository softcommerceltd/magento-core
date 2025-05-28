<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface IsCatalogProductSuperLinkInterface used to
 * check if product is linked to configurable variation.
 */
interface IsCatalogProductSuperLinkInterface
{
    /**
     * @param string $sku
     * @return bool
     * @throws LocalizedException
     */
    public function executeAllBySku(string $sku): bool;

    /**
     * Returns true if $productId is a simple child of at least one configurable.
     * @param int $productId
     * @return bool
     */
    public function executeSingle(int $productId): bool;

    /**
     * @param int[] $productIds
     * @return array [ id => bool, … ]
     */
    public function executeBatch(array $productIds): array;
}
