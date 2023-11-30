<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SoftCommerce\Core\Framework\String;

/**
 * Interface ParseStringInterface used to
 * parse string to a value
 */
interface ParseStringInterface
{
    public const SPACE_TO_UNDERSCORE_ARG = [[' '], ['_']];

    /**
     * @param string $subject
     * @param array $searchReplaceElements [['search_elem_1','search_elem_2'], ['replace_elem_1','replace_elem_2']]
     * @return string
     */
    public function execute(string $subject, array $searchReplaceElements = []): string;
}
