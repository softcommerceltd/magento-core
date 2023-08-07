<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * @inheritDoc
 */
class FieldHeading extends Field
{
    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element): string
    {
        $resultHtml = "<tr id=\"row_{$element->getHtmlId()}\">";
        $resultHtml .= '<td class="label"></td>';
        $resultHtml .= '<td class="value">';
        $resultHtml .= '<div class="sc-field-title">' . $element->getData('label') . '</div>';
        $resultHtml .= '<div class="sc-field-content">';
        $resultHtml .= '<div id="sc-field-content-inner">' . $element->getData('comment') . '</div>';
        $resultHtml .= '</div>';
        $resultHtml .= '</td>';
        $resultHtml .= '<td>';
        $resultHtml .= '</td>';
        $resultHtml .= '</tr>';

        return $resultHtml;
    }
}
