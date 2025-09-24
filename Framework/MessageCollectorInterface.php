<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework;

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
     * @param string $entity Entity identifier (e.g., order increment ID)
     * @param string $message The message text
     * @param string $status Status: success, error, warning, info
     * @param array $metadata Additional structured data about the operation
     * @return void
     */
    public function addMessage(
        string $entity,
        string $message,
        string $status = 'info',
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
     * @param string $entity
     * @return array
     */
    public function getEntityMessages(string $entity): array;

    /**
     * Get summary statistics
     *
     * @return array Array with total, success, error, warning counts per entity
     */
    public function getStatistics(): array;

    /**
     * Reset all collected data
     *
     * @return void
     */
    public function reset(): void;
}