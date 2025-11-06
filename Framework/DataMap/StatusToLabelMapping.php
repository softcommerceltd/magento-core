<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\DataMap;

use SoftCommerce\Core\Model\Source\Status\IsActive;
use SoftCommerce\Core\Model\Source\StatusInterface;

/**
 * @inheritDoc
 */
class StatusToLabelMapping implements StatusToLabelMappingInterface
{
    /**
     * @param StatusInterface $status
     * @param IsActive $isActiveStatus
     */
    public function __construct(
        private readonly StatusInterface $status,
        private readonly IsActive $isActiveStatus
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute($status)
    {
        if ($result = $this->status->getOptions()[$status] ?? null) {
            return $result;
        }

        if (is_numeric($status)
            && $result = $this->isActiveStatus->getOptions()[(int) $status] ?? null
        ) {
            return $result;
        }

        return __('Unknown Status Map');
    }
}
