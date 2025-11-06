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
     * @param array $data
     */
    public function __construct(
        protected array $data = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getData(int|string|null $entity = null, int|string|null $index = null): array|string|null
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
    public function setData(mixed $data, int|string $entity, array|int|string|null $index = null): void
    {
        if (is_array($index)) {
            $this->setMultidimensionalData($data, $entity, $index);
            return;
        }

        null !== $index
            ? $this->data[$entity][$index] = $data
            : $this->data[$entity] = $data;
    }

    /**
     * @inheritDoc
     */
    public function addData(mixed $data, int|string $entity, array|int|string|null $index = null): void
    {
        if (is_array($index)) {
            $this->setMultidimensionalData([$data], $entity, $index);
            return;
        }

        null !== $index
            ? $this->data[$entity][$index][] = $data
            : $this->data[$entity][] = $data;
    }

    /**
     * @inheritDoc
     */
    public function mergeData(mixed $data, int|string $entity, array|int|string|null $index = null): void
    {
        if (is_array($index)) {
            $this->setMultidimensionalData([$data], $entity, $index);
            return;
        }

        null !== $index
            ? $this->data[$entity][$index] = array_merge($this->data[$entity][$index] ?? [], $data)
            : $this->data[$entity] = array_merge($this->data[$entity] ?? [], $data);
    }

    /**
     * @inheritDoc
     */
    public function mergeRecursiveData(mixed $data, int|string $entity, array|int|string|null $index = null): void
    {
        if (is_array($index)) {
            $this->setMultidimensionalData([$data], $entity, $index);
            return;
        }

        null !== $index
            ? $this->data[$entity][$index] = array_merge_recursive($this->data[$entity][$index] ?? [], $data)
            : $this->data[$entity] = array_merge_recursive($this->data[$entity] ?? [], $data);
    }

    /**
     * @inheritDoc
     */
    public function resetData(int|string|null $entity = null, array|int|string|null $index = null): void
    {
        if (null === $entity) {
            $this->data = [];
            return;
        }

        if (null === $index) {
            unset($this->data[$entity]);
            return;
        }

        foreach (is_array($index) ? $index : [$index] as $key) {
            unset($this->data[$entity][$key]);
        }
    }

    /**
     * @param mixed $data
     * @param int|string $entity
     * @param array $indexes
     * @return void
     */
    private function setMultidimensionalData(mixed $data, int|string $entity, array $indexes): void
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
    }
}
