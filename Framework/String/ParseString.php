<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\String;

use function mb_strtolower;
use function str_replace;
use function trim;

/**
 * @inheritDoc
 */
class ParseString implements ParseStringInterface
{
    /**
     * @inheritDoc
     */
    public function execute(string $subject, array $searchReplaceElements = []): string
    {
        $subject = trim($subject);

        if ($searchReplaceElements) {
            list ($search, $replace) = $searchReplaceElements;
            $subject = str_replace($search, $replace, $subject);
        }

        return mb_strtolower($subject);
    }
}
