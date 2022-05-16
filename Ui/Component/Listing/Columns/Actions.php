<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Ui\Component\Listing\Columns;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * @inheritDoc
 */
class Actions extends Column
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param Escaper $escaper
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Escaper $escaper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $identifierField = $this->getData('config/identifierField') ?: 'entity_id';
        $urlPath = $this->getData('config/urlPath') ?: '#';
        $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'id';
        $label = $this->getData('config/actionLabel') ?: __('Manage');
        $confirmMessage = $this->getData('config/confirmMessage');
        $confirmTitle = $this->getData('config/confirmTitle') ?: __('Confirm current action');
        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item[$identifierField])) {
                continue;
            }

            $item[$this->getData('name')] = [
                'action' => [
                    'href' => $this->urlBuilder->getUrl(
                        $urlPath,
                        [
                            $urlEntityParamName => $item[$identifierField],
                        ]
                    ),
                    'label' => $label
                ]
            ];

            if (null !== $confirmMessage) {
                $item[$this->getData('name')]['action']['confirm'] = [
                    'title' => __($this->escaper->escapeHtml($confirmTitle)),
                    'message' => __($this->escaper->escapeHtml($confirmMessage))
                ];
            }
        }

        return $dataSource;
    }
}
