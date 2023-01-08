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
    private ProductTaxClassSource $productTaxClassSource;

    /**
     * @param ProductTaxClassSource $productTaxClassSource
     */
    public function __construct(ProductTaxClassSource $productTaxClassSource)
    {
        $this->productTaxClassSource = $productTaxClassSource;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return $this->productTaxClassSource->getAllOptions();
    }
}
