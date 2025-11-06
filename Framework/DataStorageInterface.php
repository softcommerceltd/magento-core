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
    public function getData(int|string|null $index = null);

    /**
     * @param mixed $data
     * @param array|int|string|null $index
     * @return DataStorageInterface
     */
    public function setData(mixed $data, array|int|string|null $index = null): static;

    /**
     * @param mixed $data
     * @param array|int|string|null $index
     * @return DataStorageInterface
     */
    public function addData(mixed $data, array|int|string|null $index = null): static;

    /**
     * @param mixed $data
     * @param array|int|string|null $index
     * @return DataStorageInterface
     */
    public function mergeData(mixed $data, array|int|string|null $index = null): static;

    /**
     * @param mixed $data
     * @param array|int|string|null $index
     * @return DataStorageInterface
     */
    public function mergeRecursiveData(mixed $data, array|int|string|null $index = null): static;

    /**
     * @param array|int|string|null $index
     * @return $this
     */
    public function unsetData(array|int|string|null $index = null): static;

    /**
     * @param int|string $identifier
     * @param int|string|null $index
     * @return array|int|string|null
     */
    public function getDataByIdentifier(int|string $identifier, int|string|null $index = null): mixed;

    /**
     * @param mixed $data
     * @param int|string $identifier
     * @param array|int|string|null $index
     * @return DataStorageInterface
     */
    public function setDataByIdentifier(mixed $data, int|string $identifier, array|int|string|null $index = null): static;

    /**
     * @param mixed $data
     * @param int|string $identifier
     * @param array|int|string|null $index
     * @return DataStorageInterface
     */
    public function addDataByIdentifier(mixed $data, int|string $identifier, array|int|string|null $index = null): static;

    /**
     * @param mixed $data
     * @param int|string $identifier
     * @param array|int|string|null $index
     * @return DataStorageInterface
     */
    public function mergeDataByIdentifier(mixed $data, int|string $identifier, array|int|string|null $index = null): static;

    /**
     * @param int|string $identifier
     * @param array|int|string|null $index
     * @return $this
     */
    public function unsetDataByIdentifier(int|string $identifier, array|int|string|null $index = null): static;

    /**
     * @param array|int|string|null $index
     * @return DataStorageInterface
     */
    public function resetData(array|int|string|null $index = null): static;

    /**
     * @param array|int|string|null $index
     * @return bool
     */
    public function hasData(array|int|string|null $index = null): bool;
}
