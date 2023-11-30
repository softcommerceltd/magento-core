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
    public function execute(string $sku): bool;
}
