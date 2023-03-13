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
    public function getData(int|string $index = null)
    {
        return null !== $index
            ? ($this->data[$index] ?? null)
            : ($this->data ?: []);
    }

    /**
     * @inheritDoc
     */
    public function setData($data, int|string|array|null $index = null)
    {
        if (is_array($index)) {
            return $this->setMultidimensionalData($data, $index);
        }

        null !== $index
            ? $this->data[$index] = $data
            : $this->data = $data;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addData($data, int|string|array|null $index = null)
    {
        if (is_array($index)) {
            return $this->setMultidimensionalData([$data], $index);
        }

        null !== $index
            ? $this->data[$index][] = $data
            : $this->data[] = $data;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mergeData($data, int|string|array|null $index = null)
    {
        if (is_array($index)) {
            return $this->setMultidimensionalData([$data], $index);
        }

        null !== $index
            ? $this->data[$index] = array_merge($this->data[$index] ?? [], is_array($data) ? $data : [$data])
            : $this->data = array_merge($this->data ?: [], is_array($data) ? $data : [$data]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mergeRecursiveData($data, int|string|array|null $index = null)
    {
        if (is_array($index)) {
            return $this->setMultidimensionalData([$data], $index);
        }

        null !== $index
            ? $this->data[$index] = array_merge_recursive(
                $this->data[$index] ?? [],
                is_array($data) ? $data : [$data]
            )
            : $this->data = array_merge_recursive($this->data ?: [], is_array($data) ? $data : [$data]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function resetData(int|string|array|null $index = null)
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
    public function hasData(int|string|array|null $index = null): bool
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
     * @param $data
     * @param array $indexes
     * @return $this
     */
    private function setMultidimensionalData($data, array $indexes)
    {
        $result = [];
        $value = &$result;
        for ($i = 0; $i < count($indexes); $i++) {
            $value = &$value[$indexes[$i]];
        }
        $value = $data;

        if ($result) {
            $index = array_key_first($result);
            $this->data[$index] = array_merge_recursive($this->data[$index] ?? [], current($result));
        }

        return $this;
    }
}
