<?php
/**
 * Copyright © Byte8 Ltd (formerly Soft Commerce). All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Trait;

/**
 * Trait BatchPurgeTrait
 *
 * Provides bounded, batched row deletion for retention/cleanup jobs.
 * Deleting large numbers of rows in a single statement holds long table
 * locks and bloats the binlog/undo logs, which can stall writes and lag
 * replicas. This trait deletes in capped batches instead.
 *
 * Builds on {@see ConnectionTrait}, so the using class only needs to
 * provide a `private ResourceConnection $resourceConnection` dependency.
 *
 * Usage example:
 * <code>
 * class MyCleanup
 * {
 *     use BatchPurgeTrait;
 *
 *     public function __construct(
 *         private ResourceConnection $resourceConnection
 *     ) {}
 *
 *     public function execute(): void
 *     {
 *         $stats = $this->purgeByAge('my_table', 'created_at', $cutoff);
 *     }
 * }
 * </code>
 */
trait BatchPurgeTrait
{
    use ConnectionTrait;

    /**
     * Delete rows from $tableName where $dateColumn < $cutoff, in bounded batches.
     *
     * Rows with a NULL $dateColumn are never matched by `col < ?` and are
     * therefore left untouched. Identifiers ($tableName, $dateColumn) are
     * expected to originate from code (table-name constants), not user input.
     *
     * @param string $tableName Logical table name; the prefix is resolved internally.
     * @param string $dateColumn Column compared against $cutoff.
     * @param string $cutoff Comparison value, e.g. a GMT date string.
     * @param int $batchSize Max rows removed per statement.
     * @param int $maxBatches Hard cap on statements per run (loop backstop).
     * @return array{rows_deleted: int, batches_run: int, exhausted: bool, duration_seconds: float}
     */
    protected function purgeByAge(
        string $tableName,
        string $dateColumn,
        string $cutoff,
        int $batchSize = 5000,
        int $maxBatches = 100
    ): array {
        $batchSize = max(1, $batchSize);
        $maxBatches = max(1, $maxBatches);

        $connection = $this->getConnection();
        $table = $connection->getTableName($tableName);
        $started = microtime(true);
        $totalDeleted = 0;
        $batch = 0;

        do {
            $batch++;
            $deleted = (int) $connection->query(
                sprintf(
                    'DELETE FROM `%s` WHERE `%s` < ? LIMIT %d',
                    $table,
                    $dateColumn,
                    $batchSize
                ),
                [$cutoff]
            )->rowCount();

            $totalDeleted += $deleted;

            if ($deleted < $batchSize) {
                break;
            }
        } while ($batch < $maxBatches);

        return [
            'rows_deleted' => $totalDeleted,
            'batches_run' => $batch,
            'exhausted' => $batch >= $maxBatches,
            'duration_seconds' => round(microtime(true) - $started, 2)
        ];
    }
}
