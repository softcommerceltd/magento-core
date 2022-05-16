<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\DataMap;

use SoftCommerce\Core\Model\Source\Status;

/**
 * Interface StatusToFaMappingInterface used to
 * map status to Fontawesome icon class.
 */
interface StatusToFaMappingInterface
{
    /**
     * @param string|null $status
     * @param string $fallBack
     * @return string
     */
    public function execute(?string $status = null, string $fallBack = Status::SUCCESS): string;
}
