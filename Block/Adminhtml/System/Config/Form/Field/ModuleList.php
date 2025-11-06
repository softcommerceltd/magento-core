<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use SoftCommerce\Core\Model\ModuleListProviderInterface;

/**
 * @inheritDoc
 */
class ModuleList extends Field
{
    protected $_template = 'SoftCommerce_Core::module-list.phtml';

    /**
     * @param ModuleListProviderInterface $moduleListProvider
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        private readonly ModuleListProviderInterface $moduleListProvider,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->moduleListProvider->getList();
    }
}
