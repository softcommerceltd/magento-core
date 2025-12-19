<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Cron;

/**
 * Interface CronHeartbeatInterface
 *
 * Provides methods to check if Magento's cron system is actively running.
 * Uses heartbeat approach by checking recent cron_schedule executions.
 */
interface CronHeartbeatInterface
{
    /**
     * Default threshold in minutes to consider cron as active
     */
    public const DEFAULT_THRESHOLD_MINUTES = 10;

    /**
     * Check if cron is actively running
     *
     * Returns true if cron has successfully executed jobs within the threshold period.
     * Uses runtime caching to avoid repeated database queries within the same request.
     *
     * @param int $thresholdMinutes Number of minutes to look back for cron activity
     * @return bool
     */
    public function isActive(int $thresholdMinutes = self::DEFAULT_THRESHOLD_MINUTES): bool;

    /**
     * Get the timestamp of the last successful cron execution
     *
     * @return string|null DateTime string or null if no executions found
     */
    public function getLastExecutionTime(): ?string;

    /**
     * Reset the runtime cache
     *
     * Useful when you need to force a fresh check.
     *
     * @return void
     */
    public function resetCache(): void;
}
