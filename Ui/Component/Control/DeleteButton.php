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
     * @var string|null
     */
    protected $aclResource;

    /**
     * @var string
     */
    protected $actionRoutePath;

    /**
     * @var string|null
     */
    protected $buttonClass;

    /**
     * @var string|null
     */
    protected $buttonLabel;

    /**
     * @var string
     */
    protected $confirmationMessage;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var string|null
     */
    protected $fontName;

    /**
     * @var string
     */
    protected $idFieldName;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var int
     */
    protected $sortOrder;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

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
        Escaper $escaper,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        string $idFieldName,
        string $actionRoutePath,
        ?string $aclResource = null,
        ?string $buttonClass = null,
        ?string $buttonLabel = null,
        ?string $confirmationMessage = null,
        ?string $fontName = null,
        ?int $sortOrder = null
    ) {
        $this->escaper = $escaper;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->idFieldName = $idFieldName;
        $this->actionRoutePath = $actionRoutePath;
        $this->aclResource = $aclResource;
        $this->buttonClass = $buttonClass;
        $this->buttonLabel = $buttonLabel;
        $this->confirmationMessage = $confirmationMessage;
        $this->fontName = $fontName;
        $this->sortOrder = $sortOrder;
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