<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use SoftCommerce\PlentyItemRestApi\Model\VariationInterface;

/**
 * @inheritDoc
 */
class WeightUnitOptions implements OptionSourceInterface
{
    public const WEIGHT_G = 'weight_g';
    public const WEIGHT_NET_G = 'weight_net_g';
    public const G_IN_LB = 453.59237;
    public const G_IN_KG = 1000;

    /**
     * @var array
     */
    public static $mapping = [
        self::WEIGHT_G => VariationInterface::WEIGHT_G,
        self::WEIGHT_NET_G => VariationInterface::WEIGHT_NET_G
    ];

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'auto',  'label' => __('Auto [System decides]')],
            ['value' => self::WEIGHT_G,  'label' => __('Gross weight')],
            ['value' => self::WEIGHT_NET_G,  'label' => __('Net weight')]
        ];
    }
}
