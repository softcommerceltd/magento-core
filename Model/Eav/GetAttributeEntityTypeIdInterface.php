<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Eav;

/**
 * Interface GetAttributeEntityTypeIdInterface
 * used to obtain Eav attribute entity type ID.
 */
interface GetAttributeEntityTypeIdInterface
{
    /**
     * @param string $entityTypeCode
     * @return int
     */
    public function execute(string $entityTypeCode): int;
}
