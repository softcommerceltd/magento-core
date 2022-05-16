<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework;

/**
 * @inheritDoc
 */
class SearchMultidimensionalArray implements SearchMultidimensionalArrayInterface
{
    /**
     * @inheritDoc
     */
    public function execute(
        $needle,
        array $haystack,
        string $columnName,
        $columnId = null
    ) {
        return array_search($needle, array_column($haystack, $columnName, $columnId));
    }
}
