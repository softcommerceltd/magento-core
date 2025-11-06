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
    public const string CRITICAL = 'critical';
    public const string ERROR = 'error';
    public const string FAILED = 'failed';
    public const string INFO = 'info';
    public const string MISSED = 'missed';
    public const string PENDING = 'pending';
    public const string PENDING_COLLECT = 'pending_collect';
    public const string COMPLETE = 'complete';
    public const string RUNNING = 'running';
    public const string PROCESSING = 'processing';
    public const string SUCCESS = 'success';
    public const string NOTICE = 'notice';
    public const string SKIPPED = 'skipped';
    public const string STOPPED = 'stopped';
    public const string CREATED = 'created';
    public const string UPDATED = 'updated';
    public const string UNKNOWN = 'unknown';
    public const string WARNING = 'warning';
    public const string NEW = 'new';
    public const string QUEUED = 'queued';

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @return array
     */
    public function toOptionArrayScheduleHistoryStatus(): array;

    /**
     * @return array
     */
    public function toOptionHashScheduleHistoryStatuses(): array;

    /**
     * @return array
     */
    public function toOptionArrayImportExportStatus(): array;

    /**
     * @return array
     */
    public function toOptionArrayExportStatus(): array;

    /**
     * @return array
     */
    public function toOptionHashImportExportStatus(): array;
}
