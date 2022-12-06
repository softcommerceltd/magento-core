<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * @inheritDoc
 */
class SnakeToTitleCaseRenderer extends Column
{
    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        $componentIndex = $this->getData('name');
        foreach ($dataSource['data']['items'] ?? [] as $index => $item) {
            if (!isset($item[$componentIndex])) {
                continue;
            }

            $value = $item[$componentIndex];
            $dataSource['data']['items'][$index][$componentIndex] = ucwords($value, ' ');
        }

        return $dataSource;
    }
}
