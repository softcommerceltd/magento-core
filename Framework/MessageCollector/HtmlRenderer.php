<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\MessageCollector;

use SoftCommerce\Core\Framework\MessageCollectorInterface;

/**
 * HTML renderer for admin interface output
 */
class HtmlRenderer implements RendererInterface
{
    /**
     * @inheritDoc
     */
    public function render(MessageCollectorInterface $collector, array $options = [])
    {
        $statistics = $collector->getStatistics();
        $messages = $collector->getMessages();

        // Return empty if no messages
        if (empty($statistics)) {
            return '';
        }

        // Force table rendering if explicitly requested
        if ($options['force_table'] ?? false) {
            return $this->renderTable($collector, $options);
        }

        // Detect if this is a simple case that should be rendered concisely
        if ($this->isSimpleCase($statistics, $messages, $options)) {
            return $this->renderSimple($messages, $statistics, $options);
        }

        // Complex case - render full table
        return $this->renderTable($collector, $options);
    }

    /**
     * Render as detailed table (for complex cases)
     *
     * @param MessageCollectorInterface $collector
     * @param array $options
     * @return string
     */
    public function renderTable(MessageCollectorInterface $collector, array $options = [])
    {
        $statistics = $collector->getStatistics();
        $messages = $collector->getMessages();

        // Return empty if no statistics (no messages were collected)
        if (empty($statistics)) {
            return '';
        }

        $html = '<div class="admin__data-grid-wrap">';
        $html .= '<table class="data-grid data-grid-draggable">';
        $html .= '<thead><tr class="data-grid-th">';
        $html .= '<th class="data-grid-cell">Entity</th>';
        $html .= '<th class="data-grid-cell">Summary</th>';
        $html .= '<th class="data-grid-cell">Status</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        $totalSuccess = 0;
        $totalErrors = 0;

        foreach ($statistics as $entity => $stats) {
            $totalSuccess += $stats['success'] ?? 0;
            $totalErrors += $stats['error'] ?? 0;

            $entityMessages = $messages[$entity] ?? [];
            $summary = $this->buildEntitySummary($entity, $entityMessages);

            $errorCount = $stats['error'] ?? 0;
            $successCount = $stats['success'] ?? 0;

            $statusClass = $errorCount > 0 ? 'grid-severity-critical' : 'grid-severity-notice';
            $statusText = $errorCount > 0
                ? sprintf('%d error(s)', $errorCount)
                : sprintf('%d successful', $successCount);

            $html .= '<tr>';
            $html .= sprintf('<td class="data-grid-cell">%s</td>', htmlspecialchars((string)$entity));
            $html .= sprintf('<td class="data-grid-cell">%s</td>', htmlspecialchars($summary));
            $html .= sprintf('<td class="data-grid-cell"><span class="%s">%s</span></td>',
                $statusClass,
                htmlspecialchars($statusText)
            );
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '<tfoot><tr>';
        $html .= sprintf(
            '<td colspan="3" class="data-grid-cell">Total: %d successful operation(s), %d error(s)</td>',
            $totalSuccess,
            $totalErrors
        );
        $html .= '</tr></tfoot>';
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render as simple, concise list (for simple cases)
     *
     * @param array $messages
     * @param array $statistics
     * @param array $options
     * @return string
     */
    private function renderSimple(array $messages, array $statistics, array $options = []): string
    {
        $totalSuccess = array_sum(array_column($statistics, 'success'));
        $totalErrors = array_sum(array_column($statistics, 'error'));
        $totalWarnings = array_sum(array_column($statistics, 'warning'));

        $html = '<div class="messages-simple-list">';

        // Render each message concisely (without indicators - Magento adds its own)
        foreach ($messages as $entity => $entityMessages) {
            foreach ($entityMessages as $msg) {
                $message = $msg['message'] ?? '';
                $status = $msg['status'] ?? 'info';

                $cssClass = match($status) {
                    'error', 'critical', 'failed' => 'message-error',
                    'warning' => 'message-warning',
                    'success', 'complete' => 'message-success',
                    default => 'message-info'
                };

                $html .= sprintf(
                    '<div class="message-item %s">%s</div>',
                    $cssClass,
                    htmlspecialchars($message)
                );
            }
        }

        $html .= '</div>';

        // Add simple inline styles
        $html .= $this->getSimpleStyles();

        return $html;
    }

    /**
     * Detect if this is a simple case that should be rendered concisely
     *
     * @param array $statistics
     * @param array $messages
     * @param array $options
     * @return bool
     */
    private function isSimpleCase(array $statistics, array $messages, array $options = []): bool
    {
        // Allow override via options
        if (isset($options['force_simple'])) {
            return (bool) $options['force_simple'];
        }

        $totalEntities = count($statistics);
        $totalMessages = array_sum(array_column($statistics, 'total'));

        // Simple if: 1-5 entities AND 1-10 total messages
        if ($totalEntities <= 5 && $totalMessages <= 10) {
            $hasErrors = array_sum(array_column($statistics, 'error')) > 0;
            $hasSuccess = array_sum(array_column($statistics, 'success')) > 0;
            $hasWarnings = array_sum(array_column($statistics, 'warning')) > 0;

            // All success OR all errors/warnings (no mixed success+error) = simple
            // We want to show simple rendering when there's a clear outcome
            return !($hasErrors && $hasSuccess);
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function renderDetails(MessageCollectorInterface $collector, array $options = [])
    {
        $messages = $collector->getMessages();
        
        $html = '<div class="message-details-container">';
        $html .= '<h3>Detailed Messages</h3>';
        
        foreach ($messages as $entity => $entityMessages) {
            $html .= sprintf('<div class="entity-messages" data-entity="%s">', htmlspecialchars((string)$entity));
            $html .= sprintf('<h4>%s</h4>', htmlspecialchars((string)$entity));
            $html .= '<ul class="message-list">';
            
            foreach ($entityMessages as $msg) {
                $status = $msg['status'] ?? 'info';
                $message = $msg['message'] ?? '';
                $timestamp = $msg['timestamp'] ?? null;
                
                $cssClass = match($status) {
                    'error', 'critical' => 'message-error',
                    'warning' => 'message-warning',
                    'success' => 'message-success',
                    default => 'message-info'
                };
                
                $html .= sprintf('<li class="%s">', $cssClass);
                $html .= htmlspecialchars($message);
                
                if ($timestamp) {
                    $html .= sprintf(' <span class="timestamp">[%s]</span>', 
                        date('Y-m-d H:i:s', $timestamp)
                    );
                }
                
                $html .= '</li>';
            }
            
            $html .= '</ul>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        // Add CSS if not already included
        if (!($options['skip_styles'] ?? false)) {
            $html .= $this->getStyles();
        }
        
        return $html;
    }

    /**
     * @inheritDoc
     */
    public function supports(string $outputType): bool
    {
        return in_array($outputType, ['html', 'web', 'admin']);
    }

    /**
     * Build summary string from entity messages
     */
    private function buildEntitySummary(int|string $entity, array $entityMessages): string
    {
        $parts = [];
        $messageTexts = [];

        foreach ($entityMessages as $msg) {
            // Extract metadata-based summary
            if (isset($msg['metadata']) && is_array($msg['metadata'])) {
                $metadata = $msg['metadata'];

                if (isset($metadata['entity_type']) && isset($metadata['plenty_id'])) {
                    $type = ucfirst($metadata['entity_type']);
                    $id = $metadata['plenty_id'];
                    $parts[$type] = $type . ' ' . $id;
                }
            }

            // Also collect actual message texts (limit to first 3)
            if (isset($msg['message']) && !empty($msg['message']) && count($messageTexts) < 3) {
                $messageTexts[] = $msg['message'];
            }
        }

        // If we have metadata-based parts, use those
        if (!empty($parts)) {
            return implode(' | ', $parts);
        }

        // Otherwise, show the actual message texts
        if (!empty($messageTexts)) {
            return implode(' | ', $messageTexts);
        }

        return '';
    }

    /**
     * Get inline styles for the HTML output
     */
    private function getStyles(): string
    {
        return '
        <style>
            .message-summary-container { margin: 20px 0; }
            .message-details-container { margin-top: 20px; }
            .entity-messages { margin-bottom: 20px; border: 1px solid #e3e3e3; padding: 15px; }
            .message-list { list-style: none; padding: 0; }
            .message-list li { padding: 5px 0; margin: 5px 0; }
            .message-error { color: #e22626; background: #fdf0ef; padding: 5px; }
            .message-warning { color: #ef672f; background: #fffbf0; padding: 5px; }
            .message-success { color: #79a22e; background: #f0f9ef; padding: 5px; }
            .message-info { color: #006bb4; background: #f5f9ff; padding: 5px; }
            .timestamp { color: #999; font-size: 0.9em; }
            .grid-severity-critical { color: #e22626; font-weight: bold; }
            .grid-severity-notice { color: #79a22e; }
        </style>
        ';
    }

    /**
     * Get inline styles for simple list rendering
     */
    private function getSimpleStyles(): string
    {
        return '
        <style>
            .messages-simple-list {
                margin: 0;
                padding: 0;
                list-style: none;
            }
            .messages-simple-list .message-item {
                padding: 4px 0;
                line-height: 1.5;
                list-style: none;
                display: block;
            }
            .messages-simple-list .message-item::before,
            .messages-simple-list .message-item::after {
                display: none !important;
                content: none !important;
            }
            .messages-simple-list .message-item:first-child { padding-top: 0; }
            .messages-simple-list .message-item:last-child { padding-bottom: 0; }
        </style>
        ';
    }

    /**
     * Backward compatibility: Render summary (calls renderTable)
     *
     * @param MessageCollectorInterface $collector
     * @param array $options
     * @return string
     * @deprecated Use render() or renderTable() instead
     */
    public function renderSummary(MessageCollectorInterface $collector, array $options = [])
    {
        return $this->renderTable($collector, $options);
    }
}