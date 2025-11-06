<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\Array;

use Magento\Framework\Filter\RemoveTags;
use Magento\Framework\Stdlib\StringUtils;
use function array_merge;
use function is_array;
use function is_string;

/**
 * @inheritDoc
 */
class FlattenArray implements FlattenArrayInterface
{
    /**
     * @var int
     */
    private int $flattenValueSize;

    /**
     * @var int|null
     */
    private ?int $maxLevel = null;

    /**
     * @param RemoveTags $removeTags
     * @param StringUtils $string
     * @param int $flattenValueSize
     */
    public function __construct(
        private readonly RemoveTags $removeTags,
        private readonly StringUtils $string,
        int $flattenValueSize = 128
    ) {
        $this->flattenValueSize = $flattenValueSize;
    }
    /**
     * @inheritDoc
     */
    public function execute(
        array $array,
        bool $shouldStripTags = false,
        int $maxLevel = 0,
        string $path = '',
        string $separator = '/'
    ): array
    {
        $this->maxLevel = $maxLevel ?: null;
        return $this->flattenArray($array, $shouldStripTags, $path, $separator);
    }

    /**
     * @param array $data
     * @param string $path
     * @param string $separator
     * @param bool $shouldStripTags
     * @return array
     */
    public function flattenArray(array $data, bool $shouldStripTags, string $path, string $separator): array
    {
        if (null !== $this->maxLevel) {
            if ($this->maxLevel === 0) {
                return [$path => $data];
            }

            $this->maxLevel -= 1;
        }

        $result = [];
        $path = $path ? $path . $separator : '';

        foreach ($data as $key => $value) {
            $fullPath = $path . $key;

            if (is_array($value)) {
                $result = array_merge(
                    $result,
                    $this->flattenArray($value, $shouldStripTags, $fullPath, $separator)
                );
                continue;
            }

            if (is_string($value)) {
                if ($shouldStripTags) {
                    $value = $this->removeTags->filter($value);
                }

                if ($this->string->strlen($value) > $this->flattenValueSize) {
                    $value = $this->string->substr($value, 0, $this->flattenValueSize) . '...';
                }
            }

            $result[$fullPath] = $value;
        }

        return $result;
    }
}
