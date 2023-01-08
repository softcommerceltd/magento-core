<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework;

use function array_merge;
use function array_key_first;
use function count;
use function current;
use function is_array;

/**
 * @inheritDoc
 */
class EntityDataStorage implements EntityDataStorageInterface
{
    /**
     * @var array
     */
    protected array $data;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function getData($entity = null, ?string $index = null)
    {
        if (null === $entity) {
            return $this->data;
        }

        return null !== $index
            ? ($this->data[$entity][$index] ?? null)
            : ($this->data[$entity] ?? []);
    }

    /**
     * @inheritDoc
     */
    public function setData($data, $entity, $index = null)
    {
        if (is_array($index)) {
            return $this->setMultidimensionalData($data, $entity, $index);
        }

        null !== $index
            ? $this->data[$entity][$index] = $data
            : $this->data[$entity] = $data;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addData($data, $entity, $index = null)
    {
        if (is_array($index)) {
            return $this->setMultidimensionalData([$data], $entity, $index);
        }

        null !== $index
            ? $this->data[$entity][$index][] = $data
            : $this->data[$entity][] = $data;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mergeData($data, $entity, $index = null)
    {
        if (is_array($index)) {
            return $this->setMultidimensionalData([$data], $entity, $index);
        }

        null !== $index
            ? $this->data[$entity][$index] = array_merge($this->data[$entity][$index] ?? [], $data)
            : $this->data[$entity] = array_merge($this->data[$entity] ?? [], $data);
        return $this;
    }

    public function mergeRecusiveData($data, $entity, $index = null)
    {
        if (is_array($index)) {
            return $this->setMultidimensionalData([$data], $entity, $index);
        }

        null !== $index
            ? $this->data[$entity][$index] = array_merge($this->data[$entity][$index] ?? [], $data)
            : $this->data[$entity] = array_merge($this->data[$entity] ?? [], $data);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function resetData($entity = null, $index = null)
    {
        if (null === $entity) {
            $this->data = [];
            return $this;
        }

        if (null === $index) {
            unset($this->data[$entity]);
            return $this;
        }

        foreach (is_array($index) ? $index : [$index] as $key) {
            unset($this->data[$entity][$key]);
        }

        return $this;
    }

    /**
     * @param $data
     * @param $entity
     * @param array $indexes
     * @return $this
     */
    private function setMultidimensionalData($data, $entity, array $indexes)
    {
        $result = [];
        $value = &$result;
        for ($i = 0; $i < count($indexes); $i++) {
            $value = &$value[$indexes[$i]];
        }
        $value = $data;

        if ($result) {
            $index = array_key_first($result);
            $this->data[$entity][$index] = array_merge_recursive($this->data[$entity][$index] ?? [], current($result));
        }

        return $this;
    }
}
