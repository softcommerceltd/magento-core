<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Eav;

/**
 * Interface GetAttributeSetByAttributeInterface used to obtain
 * EAV attribute sets by attribute.
 */
interface GetAttributeSetByAttributeInterface
{
    /**
     * @param int $attributeId
     * @return array
     */
    public function execute(int $attributeId): array;

    /**
     * @param int|null $attributeId
     * @return void
     */
    public function resetData(?int $attributeId = null): void;
}
