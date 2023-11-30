<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Customer;

/**
 * Interface GetCustomerRawDataInterface
 * used to provide customer data source as array.
 */
interface GetCustomerRawDataInterface
{
    /**
     * @param string $email
     * @param int|null $websiteId
     * @param string|string[] $cols
     * @return array|[[]]
     */
    public function execute(string $email, ?int $websiteId = null, $cols = '*'): array;
}
