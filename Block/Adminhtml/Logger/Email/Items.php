<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Block\Adminhtml\Logger\Email;

use Magento\Framework\View\Element\Template;
use SoftCommerce\Core\Framework\MessageStorage\OutputHtmlInterface;

/**
 * @inheritDoc
 */
class Items extends Template
{
    /**
     * @var OutputHtmlInterface
     */
    private OutputHtmlInterface $outputHtml;

    /**
     * @param OutputHtmlInterface $outputHtml
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        OutputHtmlInterface $outputHtml,
        Template\Context $context,
        array $data = []
    ) {
        $this->outputHtml = $outputHtml;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        $items = [];
        foreach ($this->getData('items') ?: [] as $item) {
            if (!isset($item['message'], $item['context'])) {
                continue;
            }
            $items[$item['message']] = $this->outputHtml->execute(
                $item['context'],
                [
                    OutputHtmlInterface::HTML_HEADER_TAG => '<h3>#%s</h3>',
                    OutputHtmlInterface::HTML_WRAPPER => 'ul',
                    OutputHtmlInterface::HTML_TAG => '<li class="status-%1">%2</li>'
                ]
            );
        }

        return $items;
    }
}
