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
     * @var ModuleListProviderInterface
     */
    private $moduleListProvider;

    /**
     * @param ModuleListProviderInterface $moduleListProvider
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        ModuleListProviderInterface $moduleListProvider,
        Template\Context $context,
        array $data = []
    ) {
        $this->moduleListProvider = $moduleListProvider;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return array
     */
    public function getList()
    {
        return $this->moduleListProvider->getList();
    }
}
