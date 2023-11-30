<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework;

/**
 * Interface EntityDataStorageInterface used to store
 * and retrieve data in array format.
 */
interface EntityDataStorageInterface
{
    /**
     * @param int|string|null $entity
     * @param int|string|null $index
     * @return array|string|null
     */
    public function getData(int|string|null $entity = null, int|string|null $index = null): array|string|null;

    /**
     * @param mixed $data
     * @param int|string $entity
     * @param array|int|string|null $index
     * @return void
     */
    public function setData(mixed $data, int|string $entity, array|int|string|null $index = null): void;

    /**
     * @param mixed $data
     * @param int|string $entity
     * @param array|int|string|null $index
     * @return void
     */
    public function addData(mixed $data, int|string $entity, array|int|string|null $index = null): void;

    /**
     * @param mixed $data
     * @param int|string $entity
     * @param array|int|string|null $index
     * @return void
     */
    public function mergeData(mixed $data, int|string $entity, array|int|string|null $index = null): void;

    /**
     * @param mixed $data
     * @param int|string $entity
     * @param array|int|string|null $index
     * @return void
     */
    public function mergeRecursiveData(mixed $data, int|string $entity, array|int|string|null $index = null): void;

    /**
     * @param int|string|null $entity
     * @param array|int|string|null $index
     * @return void
     */
    public function resetData(int|string|null $entity = null, array|int|string|null $index = null): void;
}
