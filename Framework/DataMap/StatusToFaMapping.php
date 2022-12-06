<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\DataMap;

use SoftCommerce\Core\Model\Source\Status;

/**
 * @inheritDoc
 */
class StatusToFaMapping implements StatusToFaMappingInterface
{
    /**
     * @var string[]
     */
    private array $map = [
        Status::CRITICAL => 'fa-solid fa-octagon-exclamation',
        Status::ERROR => 'fa-solid fa-circle-exclamation',
        Status::FAILED => 'fa-solid fa-circle-exclamation',
        Status::NOTICE => 'fa-solid fa-exclamation',
        Status::WARNING => 'fa-solid fa-triangle-exclamation',
        Status::PENDING => 'far fa-clock',
        Status::COMPLETE => 'fas fa-check',
        Status::SUCCESS => 'fas fa-check',
        Status::MISSED => 'fas fa-circle-notch',
        Status::SKIPPED => 'fas fa-circle-notch',
        Status::STOPPED => 'fas fa-ban',
        Status::PROCESSING => 'fas fa-circle-notch fa-spin',
        Status::RUNNING => 'fas fa-circle-notch fa-spin',
        Status::UPDATED => 'fas fa-sync-alt',
        Status\IsActive::ENABLED => 'fas fa-toggle-on',
        Status\IsActive::DISABLED => 'fas fa-toggle-off',
    ];

    /**
     * @inheritDoc
     */
    public function execute(?string $status = null, string $fallBack = Status::SUCCESS): string
    {
        if (is_numeric($status)) {
            $status = (int) $status;
        }

        return $this->map[$status] ?? ($this->map[$fallBack] ?? '');
    }
}
