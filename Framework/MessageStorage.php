<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework;

use SoftCommerce\Core\Model\Source\StatusInterface;
use function array_filter;
use function array_merge_recursive;
use function array_keys;
use function array_unique;
use function in_array;

/**
 * @inheritDoc
 */
class MessageStorage implements MessageStorageInterface
{
    /**
     * @var array
     */
    private array $data = [];

    /**
     * @inheritDoc
     */
    public function getData($entity = null, array $status = []): array
    {
        $data = null !== $entity
            ? ($this->data[$entity] ?? [])
            : $this->data;

        if (empty($status)) {
            return $data;
        }

        $resultData = [];
        foreach ($data as $index => $items) {
            if (!is_array($items)) {
                if (in_array($items, $status)) {
                    $resultData[$index] = $items;
                }
                continue;
            }

            if (isset($items[self::STATUS]) && in_array($items[self::STATUS], $status)) {
                $resultData[$index] = $items;
                continue;
            }

            $result = array_filter($items, function ($item) use ($status) {
                return is_array($item)
                    && isset($item[self::STATUS])
                    && in_array($item[self::STATUS], $status);
            });

            if ($result) {
                $resultData[$index] = $result;
            }
        }

        return $resultData;
    }

    /**
     * @inheritDoc
     */
    public function getDataByStatus(string $status): array
    {
        $result = [];
        foreach ($this->getData() as $entity => $items) {
            foreach ($items as $index => $item) {
                if ($status === ($item[self::STATUS] ?? '')) {
                    $result[$entity][$index] = $item;
                }
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function addData($message, $entity, string $status = StatusInterface::SUCCESS, array $metadata = []): static
    {
        $data = [
            self::ENTITY => $entity,
            self::STATUS => $status,
            self::MESSAGE => $message
        ];
        
        if (!empty($metadata)) {
            $data[self::METADATA] = $metadata;
        }
        
        $this->data[$entity][] = $data;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mergeData(array $data, $key = null): static
    {
        null !== $key
            ? $this->data[$key] = array_merge_recursive($this->data[$key] ?? [], $data[$key] ?? [])
            : $this->data = array_merge_recursive($this->data, $data);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEntityIds(): array
    {
        return array_unique(array_keys($this->data));
    }

    /**
     * @inheritDoc
     */
    public function resetData($key = null): static
    {
        if (null === $key) {
            $this->data = [];
        } elseif (isset($this->data[$key])) {
            unset($this->data[$key]);
        }

        return $this;
    }
}
