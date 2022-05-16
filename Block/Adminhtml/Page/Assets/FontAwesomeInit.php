<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Block\Adminhtml\Page\Assets;

use Magento\Backend\Block\Template;

/**
 * @inheritDoc
 */
class FontAwesomeInit extends Template
{
    private const DEFAULT_SOURCE = 'https://use.fontawesome.com/releases/v6.1.1/css/all.css';
    private const XML_PATH_IS_ACTIVE = 'softcommerce_core/ui/is_active_fontawesome';
    private const XML_PATH_SOURCE = 'softcommerce_core/ui/fontawesome_resource';

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        if ($this->_scopeConfig->isSetFlag(self::XML_PATH_IS_ACTIVE)) {
            $this->pageConfig->addRemotePageAsset(
                $this->_scopeConfig->getValue(self::XML_PATH_SOURCE) ?: self::DEFAULT_SOURCE,
                'css'
            );
        }

        return parent::_prepareLayout();
    }
}
