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
use Magento\Framework\UrlInterface;

/**
 * @inheritDoc
 */
class SaveSplitButtonExtended implements ButtonProviderInterface
{
    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param string $targetName
     * @param string|null $aclResource
     * @param int|null $sortOrder
     * @param array $customOptions
     */
    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly UrlInterface $urlBuilder,
        protected string $targetName,
        protected ?string $aclResource = null,
        protected ?int $sortOrder = null,
        protected array $customOptions = []
    ) {}

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

        $options = [
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

        // Merge custom options if provided
        if (!empty($this->customOptions)) {
            // Process custom options for URL support
            $processedOptions = [];
            foreach ($this->customOptions as $key => $option) {
                if (isset($option['url'])) {
                    // Generate the full URL
                    $url = $this->urlBuilder->getUrl($option['url']);

                    // For URL-based actions, we need to use the redirect action
                    if (isset($option['confirm'])) {
                        // For confirmations, we'll need to use the form's custom method
                        $option['data_attribute'] = [
                            'mage-init' => [
                                'buttonAdapter' => [
                                    'actions' => [
                                        [
                                            'targetName' => $this->targetName,
                                            'actionName' => 'redirect',
                                            'params' => [
                                                'url' => $url,
                                                'confirm' => [
                                                    'title' => __('Confirm'),
                                                    'message' => __($option['confirm'])
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    } else {
                        // For simple redirects
                        $option['data_attribute'] = [
                            'mage-init' => [
                                'buttonAdapter' => [
                                    'actions' => [
                                        [
                                            'targetName' => $this->targetName,
                                            'actionName' => 'redirect',
                                            'params' => [
                                                'url' => $url
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    }

                    // Clean up
                    unset($option['url']);
                    unset($option['confirm']);
                }
                $processedOptions[] = $option;
            }
            $options = array_merge($options, $processedOptions);
        }

        return $options;
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
