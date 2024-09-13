<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\Array;

/**
 * Interface FlattenArrayInterface used to expand
 * multidimensional array into flat structure
 */
interface FlattenArrayInterface
{
    /**
     * @param array $array
     * @param bool $shouldStripTags
     * @param string $path
     * @param string $separator
     * @return array
     */
    public function execute(
        array $array,
        bool $shouldStripTags = false,
        string $path = '',
        string $separator = '/'
    ): array;
}
