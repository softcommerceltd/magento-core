<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\MessageCollector;

use SoftCommerce\Core\Framework\MessageCollectorInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * Console renderer for CLI output
 */
class ConsoleRenderer implements RendererInterface
{
    public function __construct(
        private OutputInterface $output
    ) {}

    /**
     * @inheritDoc
     */
    public function render(MessageCollectorInterface $collector, array $options = [])
    {
        $verbose = $options['verbose'] ?? false;
        
        // Render summary first
        $this->renderSummary($collector, $options);
        
        // Render details if verbose
        if ($verbose) {
            $this->renderDetails($collector, $options);
        }
    }

    /**
     * @inheritDoc
     */
    public function renderSummary(MessageCollectorInterface $collector, array $options = [])
    {
        $statistics = $collector->getStatistics();
        $messages = $collector->getMessages();
        
        $this->output->writeln('');
        $this->output->writeln('<info>Export Summary:</info>');
        
        $totalEntities = 0;
        $totalSuccess = 0;
        $totalErrors = 0;
        
        foreach ($statistics as $entity => $stats) {
            $totalEntities++;
            $totalSuccess += $stats['success'];
            $totalErrors += $stats['error'];
            
            // Build entity summary from metadata
            $entityMessages = $messages[$entity] ?? [];
            $summary = $this->buildEntitySummary($entity, $entityMessages);
            
            if ($summary) {
                $statusIcon = $stats['error'] > 0 ? '<error>✗</error>' : '<info>✓</info>';
                $this->output->writeln(sprintf('  %s %s: %s', $statusIcon, $entity, $summary));
            }
        }
        
        $this->output->writeln('');
        $this->output->writeln(sprintf(
            '<info>Total: %d entities processed (%d successful operations, %d errors)</info>',
            $totalEntities,
            $totalSuccess,
            $totalErrors
        ));
    }

    /**
     * @inheritDoc
     */
    public function renderDetails(MessageCollectorInterface $collector, array $options = [])
    {
        $messages = $collector->getMessages();
        
        $this->output->writeln('');
        $this->output->writeln('<comment>Detailed Messages:</comment>');
        
        foreach ($messages as $entity => $entityMessages) {
            $this->output->writeln('');
            $this->output->writeln(sprintf('<comment>%s:</comment>', $entity));
            
            foreach ($entityMessages as $msg) {
                $status = $msg['status'] ?? 'info';
                $message = $msg['message'] ?? '';
                $timestamp = $msg['timestamp'] ?? null;
                
                $icon = match($status) {
                    'error', 'critical' => '<error>✗</error>',
                    'warning' => '<comment>⚠</comment>',
                    default => '<info>✓</info>'
                };
                
                $line = sprintf('  %s %s', $icon, $message);
                if ($timestamp) {
                    $line .= sprintf(' <fg=gray>[%s]</>', date('H:i:s', $timestamp));
                }
                
                $this->output->writeln($line);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function supports(string $outputType): bool
    {
        return in_array($outputType, ['cli', 'console', 'terminal']);
    }

    /**
     * Build summary string from entity messages
     */
    private function buildEntitySummary(string $entity, array $entityMessages): string
    {
        $parts = [];
        
        foreach ($entityMessages as $msg) {
            if (!isset($msg['metadata']) || !is_array($msg['metadata'])) {
                continue;
            }
            
            $metadata = $msg['metadata'];
            
            // Extract entity information from metadata
            if (isset($metadata['entity_type']) && isset($metadata['plenty_id'])) {
                $type = ucfirst($metadata['entity_type']);
                $id = $metadata['plenty_id'];
                $parts[$type] = $type . ' ' . $id;
            }
        }
        
        return implode(' | ', $parts);
    }
}