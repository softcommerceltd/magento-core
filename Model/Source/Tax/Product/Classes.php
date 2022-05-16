<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Source\Tax\Product;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Tax\Model\TaxClass\Source\Product as ProductTaxClassSource;

/**
 * @inheritDoc
 */
class Classes implements OptionSourceInterface
{
    /**
     * @var ProductTaxClassSource
     */
    private $productTaxClassSource;

    /**
     * Classes constructor.
     * @param ProductTaxClassSource $productTaxClassSource
     */
    public function __construct(ProductTaxClassSource $productTaxClassSource)
    {
        $this->productTaxClassSource = $productTaxClassSource;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->productTaxClassSource->getAllOptions();
    }
}
