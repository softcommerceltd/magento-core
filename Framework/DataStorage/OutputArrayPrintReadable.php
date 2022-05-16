<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\DataStorage;

/**
 * @inheritDoc
 */
class OutputArrayPrintReadable implements OutputArrayPrintReadableInterface
{
    /**
     * @var string
     */
    private $html;

    /**
     * @inheritDoc
     */
    public function execute(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $this->html = '<ul class="data-tree parent-node">';
        $this->generateHtmlOutput($data);
        $this->html .= '</ul>';
        return $this->html;
    }

    /**
     * @param array $data
     * @return void
     */
    private function generateHtmlOutput(array $data): void
    {
        foreach ($data as $index => $element) {
            if (is_array($element)) {
                if (is_string($index)) {
                    $label = ucfirst($index);
                    $this->html .= "<li><i class='fas fa-level-down-alt child-node-title'></i>";
                    $this->html .= "<b class='child-node-title pl-1'>$label</b><ul class='data-tree child-node'>";
                }
                $this->generateHtmlOutput($element);
                if (is_string($index)) {
                    $this->html .= '</li></ul>';
                }
                continue;
            }

            $this->html .= '<li>';
            if (is_string($index)) {
                $this->html .= "<b>$index</b>:";
            }
            $this->html .= "<span class='pl-1'>$element</span></li>";
        }
    }
}
