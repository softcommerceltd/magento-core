<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\DataMap;

use SoftCommerce\Core\Model\Source\Status;
use SoftCommerce\Core\Model\Source\StatusInterface;

/**
 * @inheritDoc
 */
class StatusToFaMapping implements StatusToFaMappingInterface
{
    /**
     * @var string[]
     */
    private array $map = [
        StatusInterface::CRITICAL => 'fa-solid fa-octagon-exclamation',
        StatusInterface::ERROR => 'fa-solid fa-circle-exclamation',
        StatusInterface::FAILED => 'fa-solid fa-circle-exclamation',
        StatusInterface::NOTICE => 'fa-solid fa-exclamation',
        StatusInterface::WARNING => 'fa-solid fa-triangle-exclamation',
        StatusInterface::PENDING => 'far fa-clock',
        StatusInterface::COMPLETE => 'fas fa-check',
        StatusInterface::SUCCESS => 'fas fa-check',
        StatusInterface::MISSED => 'fas fa-circle-notch',
        StatusInterface::SKIPPED => 'fas fa-circle-notch',
        StatusInterface::STOPPED => 'fas fa-ban',
        StatusInterface::PROCESSING => 'fas fa-circle-notch fa-spin',
        StatusInterface::RUNNING => 'fas fa-circle-notch fa-spin',
        StatusInterface::UPDATED => 'fas fa-sync-alt',
        StatusInterface::NEW => 'fa-solid fa-clock-rotate-left',
        Status\IsActive::ENABLED => 'fas fa-toggle-on',
        Status\IsActive::DISABLED => 'fas fa-toggle-off',
    ];

    /**
     * @inheritDoc
     */
    public function execute(?string $status = null, string $fallBack = StatusInterface::SUCCESS): string
    {
        if (is_numeric($status)) {
            $status = (int) $status;
        }

        return $this->map[$status] ?? ($this->map[$fallBack] ?? '');
    }
}
