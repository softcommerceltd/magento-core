<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Ui\Component\Listing\Columns;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use SoftCommerce\Core\Framework\DataStorage\OutputArrayPrintReadableInterface;
use SoftCommerce\Core\Framework\DataStorage\StatusPredictionInterface;

/**
 * @inheritDoc
 */
class ModalContentRenderer extends Column
{
    /**
     * @var OutputArrayPrintReadableInterface
     */
    private OutputArrayPrintReadableInterface $outputArrayPrintReadable;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var StatusPredictionInterface
     */
    private StatusPredictionInterface $statusPrediction;

    /**
     * @param OutputArrayPrintReadableInterface $outputArrayPrintReadable
     * @param SerializerInterface $serializer
     * @param StatusPredictionInterface $statusPrediction
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        OutputArrayPrintReadableInterface $outputArrayPrintReadable,
        SerializerInterface $serializer,
        StatusPredictionInterface $statusPrediction,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->outputArrayPrintReadable = $outputArrayPrintReadable;
        $this->serializer = $serializer;
        $this->statusPrediction = $statusPrediction;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        $componentIndex = $this->getData('name');
        foreach ($dataSource['data']['items'] ?? [] as $index => $item) {
            if (!$data = $item[$componentIndex] ?? null) {
                continue;
            }

            try {
                $data = $this->serializer->unserialize($data);
            } catch (\InvalidArgumentException $e) {
                $data = [$data];
            }

            $html = $this->outputArrayPrintReadable->execute($data);
            $dataSource['data']['items'][$index][$componentIndex] = $html;

            if ($status = $this->statusPrediction->execute($data, '')) {
                $dataSource['data']['items'][$index]['cell_status'] = $status;
            }
        }

        return $dataSource;
    }
}
