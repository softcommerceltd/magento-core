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
use function explode;
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
    public function getData($key = null)
    {
        return null !== $key
            ? ($this->data[$key] ?? null)
            : ($this->data ?: []);
    }

    /**
     * @inheritDoc
     */
    public function setData($data, $key = null)
    {
        if (is_array($key)) {
            return $this->setMultidimensionalData($data, $key);
        }

        null !== $key
            ? $this->data[$key] = $data
            : $this->data = $data;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addData($data, $key = null)
    {
        null !== $key
            ? $this->data[$key][] = $data
            : $this->data[] = $data;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mergeData($data, $key = null)
    {
        null !== $key
            ? $this->data[$key] = array_merge($this->data[$key] ?? [], is_array($data) ? $data : [$data])
            : $this->data = array_merge($this->data ?: [], is_array($data) ? $data : [$data]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mergeRecursiveData($data, $key = null)
    {
        null !== $key
            ? $this->data[$key] = array_merge_recursive(
                $this->data[$key] ?? [],
                is_array($data) ? $data : [$data]
            )
            : $this->data = array_merge_recursive($this->data ?: [], is_array($data) ? $data : [$data]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function resetData($key = null)
    {
        if (null === $key) {
            $this->data = [];
        } elseif (isset($this->data[$key])) {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasData($key = null): bool
    {
        if (null !== $key) {
            return isset($this->data);
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
