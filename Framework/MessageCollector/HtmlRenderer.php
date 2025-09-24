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
        $html = '<div class="message-summary-container">';
        $html .= $this->renderSummary($collector, $options);
        
        if ($options['show_details'] ?? false) {
            $html .= $this->renderDetails($collector, $options);
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * @inheritDoc
     */
    public function renderSummary(MessageCollectorInterface $collector, array $options = [])
    {
        $statistics = $collector->getStatistics();
        $messages = $collector->getMessages();
        
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
            $totalSuccess += $stats['success'];
            $totalErrors += $stats['error'];
            
            $entityMessages = $messages[$entity] ?? [];
            $summary = $this->buildEntitySummary($entity, $entityMessages);
            
            $statusClass = $stats['error'] > 0 ? 'grid-severity-critical' : 'grid-severity-notice';
            $statusText = $stats['error'] > 0 
                ? sprintf('%d errors', $stats['error'])
                : sprintf('%d successful', $stats['success']);
            
            $html .= '<tr>';
            $html .= sprintf('<td class="data-grid-cell">%s</td>', htmlspecialchars($entity));
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
            '<td colspan="3" class="data-grid-cell">Total: %d successful operations, %d errors</td>',
            $totalSuccess,
            $totalErrors
        );
        $html .= '</tr></tfoot>';
        $html .= '</table>';
        $html .= '</div>';
        
        return $html;
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
            $html .= sprintf('<div class="entity-messages" data-entity="%s">', htmlspecialchars($entity));
            $html .= sprintf('<h4>%s</h4>', htmlspecialchars($entity));
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
    private function buildEntitySummary(string $entity, array $entityMessages): string
    {
        $parts = [];
        
        foreach ($entityMessages as $msg) {
            if (!isset($msg['metadata']) || !is_array($msg['metadata'])) {
                continue;
            }
            
            $metadata = $msg['metadata'];
            
            if (isset($metadata['entity_type']) && isset($metadata['plenty_id'])) {
                $type = ucfirst($metadata['entity_type']);
                $id = $metadata['plenty_id'];
                $parts[$type] = $type . ' ' . $id;
            }
        }
        
        return implode(' | ', $parts);
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
}