<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\DB;

/**
 * Interface QueryResultDataTypeConverterInterface
 * used to cast query result data to strict types.
 */
interface QueryResultDataTypeConverterInterface
{
    /**
     * @param string $dbTableName
     * @param array $data
     * @return array
     */
    public function execute(string $dbTableName, array $data): array;
}
