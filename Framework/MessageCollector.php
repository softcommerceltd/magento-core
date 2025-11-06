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
 * Implementation of MessageCollectorInterface
 *
 * Format-agnostic message collection for multi-channel output
 */
class MessageCollector implements MessageCollectorInterface
{
    /**
     * @var array Collected messages organized by entity
     */
    private array $messages = [];

    /**
     * @var array Statistics per entity
     */
    private array $statistics = [];

    /**
     * @inheritDoc
     */
    public function addMessage(
        int|string $entity,
        string|Phrase $message,
        string $status = StatusInterface::INFO,
        array $metadata = []
    ): void {
        // Initialize entity if not exists
        if (!isset($this->messages[$entity])) {
            $this->messages[$entity] = [];
            $this->statistics[$entity] = [
                'total' => 0,
                StatusInterface::SUCCESS => 0,
                StatusInterface::ERROR => 0,
                StatusInterface::WARNING => 0,
                StatusInterface::INFO => 0
            ];
        }

        // Convert Phrase to string if needed
        if ($message instanceof Phrase) {
            $message = $message->render();
        }

        // Store the message with all its data
        $this->messages[$entity][] = [
            'message' => $message,
            'status' => $status,
            'metadata' => $metadata,
            'timestamp' => time()
        ];

        // Update statistics
        $this->statistics[$entity]['total']++;

        // Increment specific status counter
        $statusKey = match($status) {
            StatusInterface::ERROR, StatusInterface::CRITICAL, StatusInterface::FAILED => StatusInterface::ERROR,
            StatusInterface::WARNING => StatusInterface::WARNING,
            StatusInterface::SUCCESS => StatusInterface::SUCCESS,
            default => StatusInterface::INFO
        };

        $this->statistics[$entity][$statusKey]++;
    }

    /**
     * @inheritDoc
     */
    public function processMessages(array $messages): void
    {
        foreach ($messages as $entity => $items) {
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $message = $item['message'] ?? '';
                $status = $item['status'] ?? StatusInterface::INFO;
                $metadata = $item['metadata'] ?? [];

                // Convert Phrase to string if needed
                if ($message instanceof \Magento\Framework\Phrase) {
                    $message = $message->render();
                }

                $this->addMessage((string)$entity, (string)$message, $status, $metadata);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @inheritDoc
     */
    public function getEntityMessages(int|string $entity): array
    {
        return $this->messages[$entity] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    /**
     * @inheritDoc
     */
    public function getEntityStatistics(int|string $entity): array
    {
        return $this->statistics[$entity] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getOverallStatus(): string
    {
        $totals = [
            StatusInterface::SUCCESS => 0,
            StatusInterface::ERROR => 0,
            StatusInterface::WARNING => 0,
            StatusInterface::INFO => 0
        ];

        // Aggregate statistics across all entities
        foreach ($this->statistics as $stats) {
            $totals[StatusInterface::SUCCESS] += $stats[StatusInterface::SUCCESS] ?? 0;
            $totals[StatusInterface::ERROR] += $stats[StatusInterface::ERROR] ?? 0;
            $totals[StatusInterface::WARNING] += $stats[StatusInterface::WARNING] ?? 0;
            $totals[StatusInterface::INFO] += $stats[StatusInterface::INFO] ?? 0;
        }

        // Has both successes and errors = partial success (some entities worked, some failed)
        if ($totals[StatusInterface::SUCCESS] > 0 && $totals[StatusInterface::ERROR] > 0) {
            return StatusInterface::WARNING;
        }

        // Has successes and warnings but no errors = successful with warnings
        if ($totals[StatusInterface::SUCCESS] > 0 && $totals[StatusInterface::WARNING] > 0) {
            return StatusInterface::WARNING;
        }

        // Has errors but NO successes = complete failure (all entities failed)
        if ($totals[StatusInterface::ERROR] > 0 && $totals[StatusInterface::SUCCESS] === 0) {
            return StatusInterface::ERROR;
        }

        // Has warnings but no successes and no errors = all skipped due to validation issues
        if ($totals[StatusInterface::WARNING] > 0
            && $totals[StatusInterface::SUCCESS] === 0
            && $totals[StatusInterface::ERROR] === 0
        ) {
            return StatusInterface::WARNING;
        }

        // Only successes (and possibly info messages) = perfect run
        if ($totals[StatusInterface::SUCCESS] > 0) {
            return StatusInterface::COMPLETE;
        }

        // Only info messages (nothing actually processed, but no errors)
        return StatusInterface::COMPLETE;
    }

    /**
     * @inheritDoc
     */
    public function clearMessages(int|string|null $entity = null): void
    {
        if ($entity === null) {
            $this->reset();
        } else {
            unset($this->messages[$entity]);
            unset($this->statistics[$entity]);
        }
    }

    /**
     * @inheritDoc
     */
    public function reset(): void
    {
        $this->messages = [];
        $this->statistics = [];
    }
}
