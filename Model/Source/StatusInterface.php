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
    public const CRITICAL = 'critical';
    public const ERROR = 'error';
    public const FAILED = 'failed';
    public const MISSED = 'missed';
    public const PENDING = 'pending';
    public const PENDING_COLLECT = 'pending_collect';
    public const COMPLETE = 'complete';
    public const RUNNING = 'running';
    public const PROCESSING = 'processing';
    public const SUCCESS = 'success';
    public const NOTICE = 'notice';
    public const SKIPPED = 'skipped';
    public const STOPPED = 'stopped';
    public const CREATED = 'created';
    public const UPDATED = 'updated';
    public const UNKNOWN = 'unknown';
    public const WARNING = 'warning';
    public const NEW = 'new';

    /**
     * @return array
     */
    public function getOptions(): array;
}
