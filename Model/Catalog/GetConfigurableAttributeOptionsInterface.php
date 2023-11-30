<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface GetConfigurableAttributeOptionsInterface used to
 * retrieve configurable product supper attribute options.
 */
interface GetConfigurableAttributeOptionsInterface
{
    /**
     * @param int $productId
     * @return array
     * @throws LocalizedException
     */
    public function execute(int $productId): array;
}
