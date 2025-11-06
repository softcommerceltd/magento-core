<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\MessageCollector;

use SoftCommerce\Core\Framework\MessageCollectorInterface;
use SoftCommerce\Core\Model\Source\StatusInterface;

/**
 * JSON renderer for AJAX/API responses
 */
class JsonRenderer implements RendererInterface
{
    /**
     * @inheritDoc
     */
    public function render(MessageCollectorInterface $collector, array $options = [])
    {
        $result = [
            'success' => true,
            'error' => false,
            'summary' => $this->renderSummary($collector, $options),
            'statistics' => $collector->getStatistics()
        ];
        
        // Categorize messages by status
        $messagesByStatus = $this->categorizeMessagesByStatus($collector);
        
        if (!empty($messagesByStatus[StatusInterface::ERROR])) {
            $result['error'] = true;
            $result['success'] = false;
            $result['errors'] = $messagesByStatus[StatusInterface::ERROR];
        }
        
        if (!empty($messagesByStatus[StatusInterface::WARNING])) {
            $result['warnings'] = $messagesByStatus[StatusInterface::WARNING];
        }
        
        if (!empty($messagesByStatus[StatusInterface::SUCCESS])) {
            $result['messages'] = $messagesByStatus[StatusInterface::SUCCESS];
        }
        
        if (!empty($messagesByStatus[StatusInterface::INFO])) {
            $result['info'] = $messagesByStatus[StatusInterface::INFO];
        }
        
        // Add detailed messages if requested
        if ($options['show_details'] ?? false) {
            $result['details'] = $this->renderDetails($collector, $options);
        }
        
        // Add items summary
        $result['items'] = $this->getItemsSummary($collector);
        
        return $result;
    }
    
    /**
     * @inheritDoc
     */
    public function renderSummary(MessageCollectorInterface $collector, array $options = [])
    {
        $statistics = $collector->getStatistics();
        $summary = [];
        
        $totalSuccess = 0;
        $totalErrors = 0;
        $totalWarnings = 0;
        
        foreach ($statistics as $entity => $stats) {
            $totalSuccess += $stats['success'] ?? 0;
            $totalErrors += $stats['error'] ?? 0;
            $totalWarnings += $stats['warning'] ?? 0;
            
            $summary[$entity] = [
                'total' => $stats['total'] ?? 0,
                'success' => $stats['success'] ?? 0,
                'error' => $stats['error'] ?? 0,
                'warning' => $stats['warning'] ?? 0
            ];
        }
        
        return [
            'totals' => [
                'success' => $totalSuccess,
                'error' => $totalErrors,
                'warning' => $totalWarnings
            ],
            'by_entity' => $summary
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function renderDetails(MessageCollectorInterface $collector, array $options = [])
    {
        $messages = $collector->getMessages();
        $details = [];
        
        foreach ($messages as $entity => $entityMessages) {
            $details[$entity] = [];
            
            foreach ($entityMessages as $msg) {
                $details[$entity][] = [
                    'message' => $msg['message'] ?? '',
                    'type' => $msg['type'] ?? StatusInterface::INFO,
                    'timestamp' => $msg['timestamp'] ?? null,
                    'metadata' => $msg['metadata'] ?? []
                ];
            }
        }
        
        return $details;
    }
    
    /**
     * @inheritDoc
     */
    public function supports(string $outputType): bool
    {
        return in_array($outputType, ['json', 'ajax', 'api']);
    }
    
    /**
     * Categorize messages by status type
     *
     * @param MessageCollectorInterface $collector
     * @return array
     */
    private function categorizeMessagesByStatus(MessageCollectorInterface $collector): array
    {
        $categorized = [
            StatusInterface::SUCCESS => [],
            StatusInterface::ERROR => [],
            StatusInterface::WARNING => [],
            StatusInterface::INFO => []
        ];
        
        foreach ($collector->getMessages() as $entity => $messages) {
            foreach ($messages as $message) {
                $status = $message['type'] ?? StatusInterface::INFO;
                $messageText = $message['message'] ?? '';
                
                if ($messageText) {
                    $formattedMessage = sprintf('[%s] %s', $this->formatEntityName($entity), $messageText);
                    $categorized[$status][] = $formattedMessage;
                }
            }
        }
        
        return $categorized;
    }
    
    /**
     * Get items summary for successful operations
     *
     * @param MessageCollectorInterface $collector
     * @return array
     */
    private function getItemsSummary(MessageCollectorInterface $collector): array
    {
        $summary = [];
        $statistics = $collector->getStatistics();
        
        foreach ($statistics as $entity => $stats) {
            if (isset($stats['success']) && $stats['success'] > 0) {
                $summary[$this->formatEntityName($entity)] = $stats['success'];
            }
        }
        
        return $summary;
    }
    
    /**
     * Format entity name for display
     *
     * @param int|string $entity
     * @return string
     */
    private function formatEntityName(int|string $entity): string
    {
        return ucwords(str_replace(['_', '.'], ' ', (string)$entity));
    }
}