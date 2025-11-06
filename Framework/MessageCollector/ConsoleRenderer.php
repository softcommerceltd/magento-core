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
        private readonly OutputInterface $output
    ) {
    }

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

        // Use operation_type from options or default to 'Export'
        $operationType = $options['operation_type'] ?? 'Export';
        $summaryTitle = ucfirst($operationType) . ' Summary:';

        // Get the ID field to use for entity identification (e.g., 'plenty_id', 'magento_id', 'entity_id')
        $idField = $options['id_field'] ?? 'entity_id';

        $this->output->writeln('');
        $this->output->writeln(sprintf('<info>%s</info>', $summaryTitle));

        $totalEntities = 0;
        $totalSuccess = 0;
        $totalErrors = 0;

        foreach ($statistics as $entity => $stats) {
            $entityMessages = $messages[$entity] ?? [];

            // Count actual records from metadata, not message count
            $recordsCollected = 0;
            foreach ($entityMessages as $msg) {
                if (isset($msg['metadata']['records_collected'])) {
                    $recordsCollected += (int) $msg['metadata']['records_collected'];
                }
            }

            // Each entity represents one processed item
            $totalEntities++;

            // Count entities with successful operations
            if ($stats['success'] > 0) {
                $totalSuccess++;
            }

            // Sum up all errors across entities
            $totalErrors += $stats['error'];

            // Build entity summary from metadata using the specified ID field
            $summary = $this->buildEntitySummary($entity, $entityMessages, $idField);

            // Always show the entity, even if metadata is incomplete
            $statusIcon = $stats['error'] > 0 ? '<error>✗</error>' : '<info>✓</info>';
            if ($summary) {
                $this->output->writeln(sprintf('  %s %s: %s', $statusIcon, $entity, $summary));
            } else {
                // Show entity ID even without metadata
                $this->output->writeln(sprintf('  %s %s: (no details available)', $statusIcon, $entity));
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
     *
     * @param int|string $entity The parent entity identifier
     * @param array $entityMessages Array of messages for this entity
     * @param string $idField The metadata field to use for IDs (e.g., 'plenty_id', 'magento_id', 'entity_id')
     * @return string The formatted summary string
     */
    private function buildEntitySummary(int|string $entity, array $entityMessages, string $idField = 'entity_id'): string
    {
        $parts = [];

        foreach ($entityMessages as $msg) {
            if (!isset($msg['metadata']) || !is_array($msg['metadata'])) {
                continue;
            }

            $metadata = $msg['metadata'];

            // Extract entity information from metadata
            if (isset($metadata['entity_type']) && isset($metadata[$idField])) {
                $type = ucwords(str_replace('_', ' ', $metadata['entity_type']));
                $id = $metadata[$idField];
                if (is_array($id)) {
                    $id = implode(', ', $id);
                }
                $parts[$type] = [
                    'type' => $type,
                    'id' => $id,
                    'raw_type' => $metadata['entity_type']
                ];
            }
        }

        // If no metadata summary was built, use the message text as fallback
        if (empty($parts) && !empty($entityMessages)) {
            $firstMessage = reset($entityMessages);
            if (isset($firstMessage['message'])) {
                // Return the message text (can be multi-line)
                return (string) $firstMessage['message'];
            }
        }

        // If we only have one entity type and it matches the parent entity, just show IDs
        // This prevents redundancy like "plenty_order: Plenty Order 7742, 7741, 7740"
        if (count($parts) === 1) {
            $part = reset($parts);
            if ($part['raw_type'] === $entity) {
                return $part['id'];
            }
        }

        // Build full summary with type labels for multiple entity types
        $summary = [];
        foreach ($parts as $part) {
            $summary[] = $part['type'] . ' ' . $part['id'];
        }

        return implode(' | ', $summary);
    }
}
