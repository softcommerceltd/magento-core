<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework;

use function array_merge;
use function array_merge_recursive;
use function array_key_first;
use function count;
use function current;
use function is_array;

/**
 * @inheritDoc
 */
class DataStorage implements DataStorageInterface
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
    public function getData(int|string|null $index = null): mixed
    {
        return null !== $index
            ? ($this->data[$index] ?? null)
            : ($this->data ?: []);
    }

    /**
     * @inheritDoc
     */
    public function setData(mixed $data, array|int|string|null $index = null): static
    {
        if (is_array($index)) {
            $this->setMultidimensionalData($data, $index);
            return $this;
        }

        if (null !== $index) {
            $this->data[$index] = $data;
        } else {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addData(mixed $data, array|int|string|null $index = null): static
    {
        if (is_array($index)) {
            $this->setMultidimensionalData([$data], $index);
            return $this;
        }

        if (null !== $index) {
            $this->data[$index][] = $data;
        } else {
            $this->data[] = $data;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mergeData(mixed $data, array|int|string|null $index = null): static
    {
        if (is_array($index)) {
            $this->setMultidimensionalData($data, $index);
            return $this;
        }

        if (null !== $index) {
            $this->data[$index] = array_merge($this->data[$index] ?? [], is_array($data) ? $data : [$data]);
        } else {
            $this->data = array_merge($this->data ?: [], is_array($data) ? $data : [$data]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mergeRecursiveData(mixed $data, array|int|string|null $index = null): static
    {
        if (is_array($index)) {
            $this->setMultidimensionalData($data, $index);
            return $this;
        }

        if (null !== $index) {
            $this->data[$index] = array_merge_recursive(
                $this->data[$index] ?? [],
                is_array($data) ? $data : [$data]
            );
        } else {
            $this->data = array_merge_recursive($this->data ?: [], is_array($data) ? $data : [$data]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unsetData(array|int|string|null $index = null): static
    {
        if (is_array($index)) {
            $this->setMultidimensionalData(null, $index);
            return $this;
        }

        if (null !== $index) {
            unset($this->data[$index]);
        } else {
            $this->data = [];
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDataByIdentifier(int|string $identifier, int|string|null $index = null): mixed
    {
        return null !== $index
            ? ($this->data[$identifier][$index] ?? null)
            : ($this->data[$identifier] ?? []);
    }

    /**
     * @inheritDoc
     */
    public function setDataByIdentifier(mixed $data, int|string $identifier, array|int|string|null $index = null): static
    {
        if (is_array($index)) {
            $this->setMultidimensionalData($data, $index, $identifier);
            return $this;
        }

        if (null !== $index) {
            $this->data[$identifier][$index] = $data;
        } else {
            $this->data[$identifier] = $data;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addDataByIdentifier(mixed $data, int|string $identifier, array|int|string|null $index = null): static
    {
        if (is_array($index)) {
            $this->setMultidimensionalData([$data], $index, $identifier);
            return $this;
        }

        if (null !== $index) {
            $this->data[$identifier][$index][] = $data;
        } else {
            $this->data[$identifier][] = $data;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mergeDataByIdentifier(mixed $data, int|string $identifier, array|int|string|null $index = null): static
    {
        if (is_array($index)) {
            $this->setMultidimensionalData($data, $index, $identifier);
            return $this;
        }

        if (null !== $index) {
            $this->data[$identifier][$index] = array_merge($this->data[$identifier][$index] ?? [], $data);
        } else {
            $this->data[$identifier] = array_merge($this->data[$identifier] ?? [], $data);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unsetDataByIdentifier(int|string $identifier, array|int|string|null $index = null): static
    {
        if (is_array($index)) {
            $this->setMultidimensionalData(null, $index, $identifier);
            return $this;
        }

        if (null !== $index) {
             unset($this->data[$identifier][$index]);
        } else {
            unset($this->data[$identifier]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function resetData(array|int|string|null $index = null): static
    {
        if (null === $index) {
            $this->data = [];
        } elseif (is_array($index)) {
            foreach ($index as $key) {
                unset($this->data[$key]);
            }
        } elseif (isset($this->data[$index])) {
            unset($this->data[$index]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasData(array|int|string|null $index = null): bool
    {
        if (null !== $index) {
            return isset($this->data[$index]);
        }

        if (is_array($index)) {
            $result = false;
            $keys = array_keys($this->data);
            foreach ($index as $key) {
                if (in_array($key, $keys)) {
                    $result = true;
                    break;
                }
            }
            return $result;
        }

        return !!$this->data;
    }

    /**
     * @param mixed $data
     * @param array $indexes
     * @param int|string|null $identifier
     * @return void
     */
    private function setMultidimensionalData(mixed $data, array $indexes, int|string|null $identifier = null): void
    {
        $result = [];
        $value = &$result;
        for ($i = 0; $i < count($indexes); $i++) {
            $value = &$value[$indexes[$i]];
        }
        $value = $data;

        if ($result) {
            $index = array_key_first($result);
            if (null !== $identifier) {
                $this->data[$identifier][$index] = array_merge_recursive(
                    $this->data[$identifier][$index] ?? [],
                    current($result)
                );
            } else {
                $this->data[$index] = array_merge_recursive($this->data[$index] ?? [], current($result));
            }
        }
    }
}
