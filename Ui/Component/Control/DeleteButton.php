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
class DeleteButton implements ButtonProviderInterface
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
     * @param string|null $fontName
     * @param int|null $sortOrder
     */
    public function __construct(
        protected readonly Escaper $escaper,
        protected readonly RequestInterface $request,
        protected readonly UrlInterface $urlBuilder,
        protected string $idFieldName,
        protected string $actionRoutePath,
        protected ?string $aclResource = null,
        protected ?string $buttonClass = null,
        protected ?string $buttonLabel = null,
        protected ?string $confirmationMessage = null,
        protected ?string $fontName = null,
        protected ?int $sortOrder = null
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getButtonData()
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

        $message = $this->escaper->escapeJs($this->escaper->escapeHtml($this->confirmationMessage));
        $data = [
            'class' => $this->buttonClass ?: 'delete',
            'class_name' => FontAwesomeButton::class,
            FontAwesomeButton::FONT_NAME => $this->fontName,
            'on_click' => "deleteConfirm('{$message}', '{$url}', {data:{{$this->idFieldName}:{$fieldId}}})",
            'sort_order' => $this->sortOrder ?: 40,
        ];

        if ($this->buttonLabel) {
            $data['label'] = $this->escaper->escapeHtml($this->buttonLabel);
        }

        if ($this->aclResource) {
            $data['aclResource'] = $this->aclResource;
        }

        return $data;
    }
}
