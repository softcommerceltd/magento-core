<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ModuleListProviderInterface used
 * to provide a list of vendor modules
 */
interface StatusInterface extends OptionSourceInterface
{
    /**
     * @return array
     */
    public function getOptions(): array;
}
