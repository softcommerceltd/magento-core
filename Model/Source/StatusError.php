<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * @inheritDoc
 */
class StatusError extends Status implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CRITICAL, 'label' => __('Critical')],
            ['value' => self::ERROR, 'label' => __('Error')],
            ['value' => self::NOTICE, 'label' => __('Notice')],
            ['value' => self::WARNING, 'label' => __('Warning')],
        ];
    }
}
