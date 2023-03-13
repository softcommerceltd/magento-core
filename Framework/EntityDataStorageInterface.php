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
     * @param string|null $index
     * @return array|mixed|null
     */
    public function getData($entity = null, ?string $index = null);

    /**
     * @param array|mixed $data
     * @param int|string $entity
     * @param int|string|array|null $index
     * @return $this
     */
    public function setData($data, $entity, $index = null);

    /**
     * @param array|mixed $data
     * @param int|string $entity
     * @param int|string|array|null $index
     * @return $this
     */
    public function addData($data, $entity, $index = null);

    /**
     * @param array|mixed $data
     * @param int|string $entity
     * @param int|string|array|null $index
     * @return $this
     */
    public function mergeData($data, $entity, $index = null);

    /**
     * @param int|string $entity
     * @param int|string|array|null $index
     * @return $this
     */
    public function resetData($entity = null, $index = null);
}
