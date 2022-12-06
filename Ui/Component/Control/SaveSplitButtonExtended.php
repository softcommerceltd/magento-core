<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Ui\Component\Control;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;

/**
 * @inheritDoc
 */
class SaveSplitButtonExtended implements ButtonProviderInterface
{
    /**
     * @var string|null
     */
    protected ?string $aclResource;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var int|null
     */
    protected ?int $sortOrder;

    /**
     * @var string
     */
    protected string $targetName;

    /**
     * @param RequestInterface $request
     * @param string $targetName
     * @param string|null $aclResource
     * @param int|null $sortOrder
     */
    public function __construct(
        RequestInterface $request,
        string $targetName,
        ?string $aclResource = null,
        ?int $sortOrder = null
    ) {
        $this->request = $request;
        $this->targetName = $targetName;
        $this->aclResource = $aclResource;
        $this->sortOrder = $sortOrder;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $buttonLabel = $this->canShowOptions()
            ? __('Save &amp; Continue')
            : __('Save');

        $data = [
            'label' => $buttonLabel,
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => $this->targetName,
                                'actionName' => 'save',
                                'params' => [
                                    // first param is redirect flag
                                    !$this->canShowOptions(),
                                    $this->getExtraParameters()
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'class_name' => Container::SPLIT_BUTTON,
            'options' => $this->getOptions(),
            'sort_order' => 40,
        ];

        return $this->processExtraParameters($data);
    }

    /**
     * @return array
     */
    private function getExtraParameters(): array
    {
        $extraParams = [];
        if ($this->isModal()) {
            $extraParams['isModal'] = 1;
        }
        if ($this->isPopup()) {
            $extraParams['popup'] = 1;
        }
        return $extraParams;
    }

    /**
     * @return array
     */
    private function getOptions(): array
    {
        if (!$this->canShowOptions()) {
            return [];
        }

        return [
            [
                'label' => __('Save &amp; Close'),
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => $this->targetName,
                                    'actionName' => 'save',
                                    'params' => [
                                        true
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'sort_order' => 10,
            ],
            [
                'label' => __('Save &amp; New'),
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => $this->targetName,
                                    'actionName' => 'save',
                                    'params' => [
                                        true,
                                        ['redirect_to_new' => 1],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'sort_order' => 20,
            ],
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    private function processExtraParameters(array $data): array
    {
        if (!empty($data)) {
            if (null !== $this->aclResource) {
                $data['aclResource'] = $this->aclResource;
            }
            if (null !== $this->sortOrder) {
                $data['sort_order'] = $this->sortOrder;
            }
        }

        return $data;
    }

    /**
     * @return bool
     */
    private function canShowOptions(): bool
    {
        return !$this->isModal() && !$this->isPopup();
    }

    /**
     * @return bool
     */
    private function isModal(): bool
    {
        return (bool) $this->request->getParam('isModal');
    }

    /**
     * @return bool
     */
    private function isPopup(): bool
    {
        return (bool) $this->request->getParam('popup');
    }
}
