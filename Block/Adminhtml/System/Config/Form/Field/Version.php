<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\PackageInfo;

/**
 * Class Version used to provide
 * data about module version
 */
class Version extends Field
{
    /**
     * @var PackageInfo
     */
    private PackageInfo $packageInfo;

    /**
     * @var string
     */
    private string $moduleName;

    /**
     * @param PackageInfo $packageInfo
     * @param Context $context
     * @param string|null $moduleName
     * @param array $data
     */
    public function __construct(
        PackageInfo $packageInfo,
        Context $context,
        ?string $moduleName = null,
        array $data = []
    ) {
        $this->packageInfo = $packageInfo;
        $this->moduleName = $moduleName ?: 'SoftCommerce_Core';
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $element->setData('text', $this->packageInfo->getVersion($this->moduleName));
        return parent::_getElementHtml($element);
    }
}
