<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\MessageStorage;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface for progress tracking in console commands
 * @deprecated in favour of
 * @see \SoftCommerce\Core\Framework\MessageCollectorInterface
 */
interface OutputConsoleProgressInterface
{
    /**
     * Set progress bar instance
     *
     * @param ProgressBar $progressBar
     * @return void
     */
    public function setProgressBar(ProgressBar $progressBar): void;

    /**
     * Set verbose mode
     *
     * @param bool $verbose
     * @return void
     */
    public function setVerboseMode(bool $verbose): void;

    /**
     * Get summary of processed items
     *
     * @return array
     */
    public function getSummary(): array;

    /**
     * Clear summary
     *
     * @return void
     */
    public function clearSummary(): void;

    /**
     * Add a message to track
     *
     * @param string $entity
     * @param string $message
     * @param string $status
     * @return void
     */
    public function addMessage(string $entity, string $message, string $status = 'info'): void;

    /**
     * Process messages and update progress
     *
     * @param OutputInterface $output
     * @param array $messages
     * @return void
     */
    public function processMessages(OutputInterface $output, array $messages): void;

    /**
     * Advance the progress bar
     *
     * @param int $step
     * @return void
     */
    public function advance(int $step = 1): void;

    /**
     * Check if in verbose mode
     *
     * @return bool
     */
    public function isVerboseMode(): bool;
}
