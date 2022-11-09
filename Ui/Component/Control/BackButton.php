<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Ui\Component\Control;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * @inheritDoc
 */
class BackButton implements ButtonProviderInterface
{
    /**
     * @var string
     */
    private string $backUrl;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param string $backUrl
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        string $backUrl
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->backUrl = $backUrl;
    }

    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        if ($this->request->getParam('isModal') || $this->request->getParam('popup')) {
            return [];
        }

        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * @return string
     */
    private function getBackUrl(): string
    {
        return $this->urlBuilder->getUrl($this->backUrl);
    }
}
