<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Cron Heartbeat
 *
 * Checks if Magento's cron system is actively running by examining
 * the cron_schedule table for recent successful executions.
 *
 * Uses runtime caching to avoid repeated database queries within the same request.
 */
class CronHeartbeat implements CronHeartbeatInterface
{
    /**
     * @var bool|null
     */
    private ?bool $isActiveCache = null;

    /**
     * @var string|null
     */
    private ?string $lastExecutionTimeCache = null;

    /**
     * @var bool
     */
    private bool $cachePopulated = false;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DateTime $dateTime
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isActive(int $thresholdMinutes = self::DEFAULT_THRESHOLD_MINUTES): bool
    {
        if ($this->isActiveCache !== null) {
            return $this->isActiveCache;
        }

        $this->populateCache($thresholdMinutes);

        return $this->isActiveCache ?? false;
    }

    /**
     * @inheritDoc
     */
    public function getLastExecutionTime(): ?string
    {
        if (!$this->cachePopulated) {
            $this->populateCache(self::DEFAULT_THRESHOLD_MINUTES);
        }

        return $this->lastExecutionTimeCache;
    }

    /**
     * @inheritDoc
     */
    public function resetCache(): void
    {
        $this->isActiveCache = null;
        $this->lastExecutionTimeCache = null;
        $this->cachePopulated = false;
    }

    /**
     * Populate cache with cron activity data
     *
     * @param int $thresholdMinutes
     * @return void
     */
    private function populateCache(int $thresholdMinutes): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('cron_schedule');

        // Calculate threshold timestamp
        $thresholdTime = $this->dateTime->gmtDate(
            'Y-m-d H:i:s',
            strtotime("-{$thresholdMinutes} minutes")
        );

        // Query for the most recent successful execution
        $select = $connection->select()
            ->from($tableName, ['executed_at'])
            ->where('status = ?', 'success')
            ->where('executed_at IS NOT NULL')
            ->order('executed_at DESC')
            ->limit(1);

        $lastExecution = $connection->fetchOne($select);

        if ($lastExecution) {
            $this->lastExecutionTimeCache = $lastExecution;
            // Check if the last execution is within the threshold
            $this->isActiveCache = strtotime($lastExecution) >= strtotime($thresholdTime);
        } else {
            $this->lastExecutionTimeCache = null;
            $this->isActiveCache = false;
        }

        $this->cachePopulated = true;
    }
}
