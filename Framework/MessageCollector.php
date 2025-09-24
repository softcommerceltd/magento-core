<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework;

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
        string $entity,
        string $message,
        string $status = 'info',
        array $metadata = []
    ): void {
        // Initialize entity if not exists
        if (!isset($this->messages[$entity])) {
            $this->messages[$entity] = [];
            $this->statistics[$entity] = [
                'total' => 0,
                'success' => 0,
                'error' => 0,
                'warning' => 0,
                'info' => 0
            ];
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
            'error', 'critical', 'failed' => 'error',
            'warning' => 'warning',
            'success' => 'success',
            default => 'info'
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
                $status = $item['status'] ?? 'info';
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
    public function getEntityMessages(string $entity): array
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
    public function reset(): void
    {
        $this->messages = [];
        $this->statistics = [];
    }
}