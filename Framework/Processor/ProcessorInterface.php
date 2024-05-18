<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\Processor;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface ProcessorInterface
 * used to process services
 */
interface ProcessorInterface extends ServiceInterface
{
    /**
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void;

    /**
     * @return ServiceInterface|null
     * @throws LocalizedException
     */
    public function getContext();

    /**
     * @param $context
     * @return $this
     */
    public function setContext($context): static;
}
