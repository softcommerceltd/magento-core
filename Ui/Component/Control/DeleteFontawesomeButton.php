<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Ui\Component\Control;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * @inheritDoc
 */
class DeleteFontawesomeButton implements ButtonProviderInterface
{
    /**
     * @param Escaper $escaper
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param string $idFieldName
     * @param string $actionRoutePath
     * @param string|null $aclResource
     * @param string|null $buttonClass
     * @param string|null $buttonLabel
     * @param string|null $confirmationMessage
     * @param int|null $sortOrder
     */
    public function __construct(
        private readonly Escaper $escaper,
        private readonly RequestInterface $request,
        private readonly UrlInterface $urlBuilder,
        private string $idFieldName,
        private string $actionRoutePath,
        private ?string $aclResource = null,
        private ?string $buttonClass = null,
        private ?string $buttonLabel = null,
        private ?string $confirmationMessage = null,
        private ?int $sortOrder = null
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        if (!$this->request->getParam($this->idFieldName)
            || $this->request->getParam('isModal')
            || $this->request->getParam('popup')
        ) {
            return [];
        }

        return $this->getData();
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        $data = [];
        if (!$fieldId = $this->escaper->escapeJs(
            $this->escaper->escapeHtml(
                $this->request->getParam($this->idFieldName)
            )
        )) {
            return $data;
        }

        $url = $this->urlBuilder->getUrl($this->actionRoutePath);

        $label = $this->escaper->escapeHtml($this->buttonLabel);
        $message = $this->escaper->escapeJs($this->escaper->escapeHtml($this->confirmationMessage));
        $data = [
            'label' => __($label),
            'class' => $this->buttonClass ?: 'delete',
            'on_click' => "deleteConfirm('{$message}', '{$url}', {data:{{$this->idFieldName}:{$fieldId}}})",
            'sort_order' => $this->sortOrder ?: 40,
        ];

        if (null !== $this->aclResource) {
            $data['aclResource'] = $this->aclResource;
        }

        return $data;
    }
}
