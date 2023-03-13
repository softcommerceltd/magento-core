<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework;

/**
 * Interface DataStorageInterface used to store
 * and retrieve data.
 */
interface DataStorageInterface
{
    /**
     * @param int|string|null $index
     * @return array|int|string|null|mixed
     */
    public function getData(int|string $index = null);

    /**
     * @param int|string|array|mixed $data
     * @param int|string|array|null $index
     * @return $this
     */
    public function setData($data, int|string|array|null $index = null);

    /**
     * @param int|string|array|mixed $data
     * @param int|string|array|null $index
     * @return $this
     */
    public function addData($data, int|string|array|null $index = null);

    /**
     * @param int|string|array|mixed $data
     * @param int|string|array|null $index
     * @return $this
     */
    public function mergeData($data, int|string|array|null $index = null);

    /**
     * @param int|string|array|mixed $data
     * @param int|string|array|null $index
     * @return $this
     */
    public function mergeRecursiveData($data, int|string|array|null $index = null);

    /**
     * @param int|string|array|null $index
     * @return $this
     */
    public function resetData(int|string|array|null $index = null);

    /**
     * @param int|string|array|null $index
     * @return bool
     */
    public function hasData(int|string|array|null $index = null): bool;
}
