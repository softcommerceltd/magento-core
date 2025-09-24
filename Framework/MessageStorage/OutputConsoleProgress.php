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
 * Progress tracker implementation for console commands
 */
class OutputConsoleProgress implements OutputConsoleProgressInterface
{
    /**
     * @var ProgressBar|null
     */
    private ?ProgressBar $progressBar = null;

    /**
     * @var array
     */
    private array $summary = [];

    /**
     * @var bool
     */
    private bool $verboseMode = false;

    /**
     * @inheritDoc
     */
    public function setProgressBar(ProgressBar $progressBar): void
    {
        $this->progressBar = $progressBar;
    }

    /**
     * @inheritDoc
     */
    public function setVerboseMode(bool $verbose): void
    {
        $this->verboseMode = $verbose;
    }

    /**
     * @inheritDoc
     */
    public function getSummary(): array
    {
        return $this->summary;
    }

    /**
     * @inheritDoc
     */
    public function clearSummary(): void
    {
        $this->summary = [];
    }

    /**
     * @inheritDoc
     */
    public function addMessage(string $entity, string $message, string $status = 'info'): void
    {
        // Initialize entity summary if not exists
        if (!isset($this->summary[$entity])) {
            $this->summary[$entity] = [
                'total' => 0,
                'success' => 0,
                'error' => 0,
                'messages' => [],
                'detailed_messages' => []
            ];
        }

        $this->summary[$entity]['total']++;
        
        if (in_array($status, ['error', 'critical', 'failed'])) {
            $this->summary[$entity]['error']++;
        } else {
            $this->summary[$entity]['success']++;
        }

        // Extract key information from message
        $key = $this->extractMessageKey($message);
        if ($key && !in_array($key, $this->summary[$entity]['messages'])) {
            $this->summary[$entity]['messages'][] = $key;
        }

        // Store detailed messages for later output if in verbose mode or for errors
        if ($this->verboseMode || in_array($status, ['error', 'critical', 'failed'])) {
            $this->summary[$entity]['detailed_messages'][] = [
                'message' => $message,
                'status' => $status
            ];
        }

        // Update progress bar if set (without outputting messages)
        if ($this->progressBar) {
            $this->progressBar->setMessage("Processing: $entity");
        }
    }

    /**
     * @inheritDoc
     */
    public function processMessages(OutputInterface $output, array $messages): void
    {
        $currentEntity = null;
        
        foreach ($messages as $items) {
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (!isset($item['entity'], $item['message'])) {
                    continue;
                }

                $entity = $item['entity'];
                $message = $item['message'];
                $status = $item['status'] ?? 'info';
                
                // Convert Phrase objects to string
                if ($message instanceof \Magento\Framework\Phrase) {
                    $message = $message->render();
                }

                // Update progress bar with current entity
                if ($currentEntity !== $entity) {
                    $currentEntity = $entity;
                    if ($this->progressBar) {
                        $this->progressBar->setMessage("Processing: $entity");
                    }
                }

                // Just collect messages, don't output them
                $this->addMessage($entity, (string)$message, $status);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function advance(int $step = 1): void
    {
        if ($this->progressBar) {
            $this->progressBar->advance($step);
        }
    }

    /**
     * @inheritDoc
     */
    public function isVerboseMode(): bool
    {
        return $this->verboseMode;
    }

    /**
     * Extract key information from message for summary
     *
     * @param string $message
     * @return string|null
     */
    private function extractMessageKey(string $message): ?string
    {
        // Extract created/updated entity info
        if (preg_match('/(Contact|Order|Payment|Address|Item).*?(?:created|updated).*?\[.*?ID:\s*(\d+)/i', $message, $matches)) {
            return $matches[1] . ' ' . $matches[2];
        }

        // Extract relation info
        if (preg_match('/(Payment-order|Payment-contact) relation.*?created/i', $message, $matches)) {
            return $matches[1] . ' relation';
        }

        return null;
    }

    /**
     * Format message for output
     *
     * @param string $entity
     * @param string $message
     * @param string $status
     * @return string
     */
    private function formatMessage(string $entity, string $message, string $status): string
    {
        $statusIcon = $this->getStatusIcon($status);
        $message = $this->condenseMessage($message);
        
        return sprintf('%s [%s] %s', $statusIcon, $entity, $message);
    }

    /**
     * Get status icon
     *
     * @param string $status
     * @return string
     */
    private function getStatusIcon(string $status): string
    {
        switch ($status) {
            case 'critical':
            case 'error':
            case 'failed':
                return '<error>✗</error>';
            case 'notice':
            case 'warning':
                return '<comment>!</comment>';
            default:
                return '<info>✓</info>';
        }
    }

    /**
     * Condense message for shorter output
     *
     * @param string $message
     * @return string
     */
    private function condenseMessage(string $message): string
    {
        // Condense contact creation
        $message = preg_replace(
            '/Contact has been created\.\s*\[Customer ID:\s*([\w-]+),\s*Contact ID:\s*(\d+)\]/i',
            'Contact: $2 (Customer: $1)',
            $message
        );

        // Condense address creation
        $message = preg_replace(
            '/Address has been created\.\s*\[Address ID:\s*(\d+),\s*Type:\s*([^]]+)\]/i',
            'Address: $1 ($2)',
            $message
        );

        // Condense order creation
        $message = preg_replace(
            '/Order has been created\.\s*\[Magento Order:\s*([\w-]+),\s*Plenty Order:\s*(\d+)\]/i',
            'Order: $2 (Magento: $1)',
            $message
        );

        // Condense payment creation
        $message = preg_replace(
            '/Payment has been created\.\s*\[Order:\s*(\d+),\s*Payment ID:\s*(\d+)\]/i',
            'Payment: $2',
            $message
        );

        // Condense relation creation
        $message = preg_replace(
            '/(Payment-order|Payment-contact) relation has been created\.\s*\[.*?Relation:\s*(\d+)\]/i',
            '$1: $2',
            $message
        );

        return $message;
    }
}