<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\MessageStorage;

/**
 * @inheritDoc
 */
class SummaryRenderer implements SummaryRendererInterface
{
    /**
     * Render summary from messages
     *
     * @param array $messages
     * @return string
     */
    public function renderSummary(array $messages): string
    {
        $parts = [];
        $entityGroups = [];

        foreach ($messages as $message) {
            if (!isset($message['metadata']['entity_type'])) {
                continue;
            }

            $entityType = $message['metadata']['entity_type'];
            $metadata = $message['metadata'];

            // Group by entity type
            if (!isset($entityGroups[$entityType])) {
                $entityGroups[$entityType] = [];
            }

            // Extract the main ID for this entity type
            $mainId = $this->extractMainId($entityType, $metadata);
            if ($mainId) {
                $entityGroups[$entityType][$mainId] = $mainId;
            }
        }

        // Build summary parts in a logical order
        $order = ['order', 'contact', 'address', 'payment', 'relation'];
        
        foreach ($order as $type) {
            if (!isset($entityGroups[$type])) {
                continue;
            }

            $ids = array_unique($entityGroups[$type]);
            if (empty($ids)) {
                continue;
            }

            // Format based on entity type
            switch ($type) {
                case 'order':
                    foreach ($ids as $id) {
                        $parts[] = 'Order ' . $id;
                    }
                    break;
                case 'contact':
                    foreach ($ids as $id) {
                        $parts[] = 'Contact ' . $id;
                    }
                    break;
                case 'address':
                    foreach ($ids as $id) {
                        $parts[] = 'Address ' . $id;
                    }
                    break;
                case 'payment':
                    foreach ($ids as $id) {
                        $parts[] = 'Payment ' . $id;
                    }
                    break;
                case 'relation':
                    // Count relations instead of listing IDs
                    $relationTypes = $this->getRelationTypes($messages);
                    foreach ($relationTypes as $relationType => $count) {
                        $parts[] = $this->formatRelationType($relationType);
                    }
                    break;
            }
        }

        return implode(' | ', $parts);
    }

    /**
     * Extract main ID for entity type
     *
     * @param string $entityType
     * @param array $metadata
     * @return string|null
     */
    private function extractMainId(string $entityType, array $metadata): ?string
    {
        // First check for direct plenty_id
        if (isset($metadata['plenty_id'])) {
            return (string) $metadata['plenty_id'];
        }

        // Then check entity_ids array
        if (isset($metadata['entity_ids'])) {
            $idsMap = [
                'order' => 'plenty_order',
                'contact' => 'plenty_contact',
                'address' => 'plenty_address',
                'payment' => 'plenty_payment',
                'item' => 'plenty_item',
                'stock' => 'plenty_warehouse'
            ];

            $key = $idsMap[$entityType] ?? null;
            if ($key && isset($metadata['entity_ids'][$key])) {
                return (string) $metadata['entity_ids'][$key];
            }
        }

        return null;
    }

    /**
     * Get relation types from messages
     *
     * @param array $messages
     * @return array
     */
    private function getRelationTypes(array $messages): array
    {
        $types = [];
        
        foreach ($messages as $message) {
            if (!isset($message['metadata']['entity_type']) || 
                $message['metadata']['entity_type'] !== 'relation') {
                continue;
            }

            $relationType = $message['metadata']['relation_type'] ?? 'unknown';
            if (!isset($types[$relationType])) {
                $types[$relationType] = 0;
            }
            $types[$relationType]++;
        }

        return $types;
    }

    /**
     * Format relation type for display
     *
     * @param string $relationType
     * @return string
     */
    private function formatRelationType(string $relationType): string
    {
        $map = [
            'payment_order' => 'Payment-order relation',
            'payment_contact' => 'Payment-contact relation',
            'item_category' => 'Item-category relation',
            'item_warehouse' => 'Item-warehouse relation'
        ];

        return $map[$relationType] ?? ucfirst(str_replace('_', '-', $relationType)) . ' relation';
    }

    /**
     * Render detailed summary with counts
     *
     * @param array $messages
     * @return array
     */
    public function renderDetailedSummary(array $messages): array
    {
        $summary = [
            'total' => count($messages),
            'by_type' => [],
            'by_action' => [],
            'entities' => []
        ];

        foreach ($messages as $message) {
            $metadata = $message['metadata'] ?? [];
            $entityType = $metadata['entity_type'] ?? 'unknown';
            $action = $metadata['action'] ?? 'processed';

            // Count by type
            if (!isset($summary['by_type'][$entityType])) {
                $summary['by_type'][$entityType] = 0;
            }
            $summary['by_type'][$entityType]++;

            // Count by action
            if (!isset($summary['by_action'][$action])) {
                $summary['by_action'][$action] = 0;
            }
            $summary['by_action'][$action]++;

            // Collect unique entities
            if (isset($metadata['entity_ids'])) {
                foreach ($metadata['entity_ids'] as $key => $value) {
                    if (!isset($summary['entities'][$key])) {
                        $summary['entities'][$key] = [];
                    }
                    $summary['entities'][$key][$value] = $value;
                }
            }
        }

        // Count unique entities
        foreach ($summary['entities'] as $key => $values) {
            $summary['entities'][$key] = count($values);
        }

        return $summary;
    }
}