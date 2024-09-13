<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * @inheritDoc
 */
class TooltipRenderer extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

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
            $dataSource['data']['items'][$index][$componentIndex] = $value;
            // $dataSource['data']['items'][$index]['cell_status'] = '$value2';
            $dataSource['data']['items'][$index]['cell_attribute'] = 'fa-regular fa-comment-dots';
        }

        return $dataSource;
    }
}
