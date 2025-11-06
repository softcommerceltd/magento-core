<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework;

use Magento\Framework\Phrase;
use SoftCommerce\Core\Model\Source\StatusInterface;

/**
 * Interface MessageCollectorInterface
 *
 * Responsible for collecting and storing messages during processing.
 * Format-agnostic - just stores raw data for later rendering.
 */
interface MessageCollectorInterface
{
    /**
     * Add a message to the collection
     *
     * @param int|string $entity Entity identifier (e.g., order increment ID)
     * @param string|Phrase $message The message text
     * @param string $status Status constant from StatusInterface (success, error, warning, info, etc.)
     * @param array $metadata Additional structured data about the operation
     * @return void
     *
     * @see StatusInterface For available status constants
     */
    public function addMessage(
        int|string $entity,
        string|Phrase $message,
        string $status = StatusInterface::INFO,
        array $metadata = []
    ): void;

    /**
     * Process batch of messages from MessageStorageInterface
     *
     * @param array $messages Array of messages from MessageStorage
     * @return void
     */
    public function processMessages(array $messages): void;

    /**
     * Get all collected messages
     *
     * @return array
     */
    public function getMessages(): array;

    /**
     * Get messages for a specific entity
     *
     * @param int|string $entity
     * @return array
     */
    public function getEntityMessages(int|string $entity): array;

    /**
     * Get summary statistics
     *
     * @return array Array with total, success, error, warning counts per entity
     */
    public function getStatistics(): array;

    /**
     * Get statistics for a specific entity
     *
     * @param int|string $entity Entity identifier
     * @return array Array with total, success, error, warning counts for the entity
     */
    public function getEntityStatistics(int|string $entity): array;

    /**
     * Determine overall status based on aggregated statistics
     *
     * Analyzes all collected messages and returns the appropriate overall status:
     * - ERROR: All operations failed (has errors, no successes)
     * - WARNING: Partial success (has both successes and errors/warnings)
     * - COMPLETE: All operations succeeded (has successes, no errors)
     * - COMPLETE: Nothing processed (only info messages)
     *
     * This method is useful for determining the final status of batch operations
     * like imports, exports, or synchronization processes.
     *
     * @return string Status constant (error, warning, complete, info)
     */
    public function getOverallStatus(): string;

    /**
     * Clear messages for a specific entity or all entities
     *
     * When entity is provided, clears messages and statistics for that specific entity only.
     * When entity is null, clears all messages and statistics (equivalent to reset()).
     *
     * @param int|string|null $entity Entity identifier, or null to clear all messages
     * @return void
     */
    public function clearMessages(int|string|null $entity = null): void;

    /**
     * Reset all collected data
     *
     * @return void
     */
    public function reset(): void;
}
