<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\ImportExport\Model\Import;

/**
 * @inheritDoc
 */
class Behaviour implements OptionSourceInterface
{
    const APPEND = 'append';
    const ADD_UPDATE = 'add_update';
    const REPLACE = 'replace';
    const DELETE = 'delete';
    const CUSTOM = 'custom';

    /**
     * Options array
     *
     * @var array
     */
    private $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                ['value' => Import::BEHAVIOR_APPEND, 'label' => __('Add/Update')],
                ['value' => Import::BEHAVIOR_REPLACE, 'label' => __('Replace')]
            ];
        }

        return $this->options;
    }
}
