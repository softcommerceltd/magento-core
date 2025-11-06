<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\MessageCollector;

use SoftCommerce\Core\Framework\MessageCollectorInterface;

/**
 * Interface RendererInterface
 * 
 * Base interface for all message renderers.
 * Implementations will handle specific output formats (CLI, HTML, JSON, etc.)
 */
interface RendererInterface
{
    /**
     * Render messages from a collector
     *
     * @param MessageCollectorInterface $collector The message collector containing messages to render
     * @param array $options Rendering options. Available options:
     *                       - 'verbose' (bool): Whether to show detailed messages. Default: false
     *                       - 'operation_type' (string): Operation type for summary title (e.g., 'Import', 'Export', 'Collect'). Default: 'Export'
     *                       - 'id_field' (string): Metadata field name to use for entity IDs (e.g., 'plenty_id', 'magento_id', 'entity_id'). Default: 'entity_id'
     * @return mixed Output depends on renderer type
     */
    public function render(MessageCollectorInterface $collector, array $options = []);

    /**
     * Render summary only
     *
     * @param MessageCollectorInterface $collector The message collector containing messages to render
     * @param array $options Rendering options. Available options:
     *                       - 'operation_type' (string): Operation type for summary title (e.g., 'Import', 'Export', 'Collect'). Default: 'Export'
     *                       - 'id_field' (string): Metadata field name to use for entity IDs (e.g., 'plenty_id', 'magento_id', 'entity_id'). Default: 'entity_id'
     *
     *                       Usage examples:
     *                       - Export operations (show Plenty IDs): ['id_field' => 'plenty_id', 'operation_type' => 'Order Export']
     *                       - Import operations (show Magento IDs): ['id_field' => 'magento_id', 'operation_type' => 'Order Import']
     *                       - Collect operations (show Plenty IDs): ['id_field' => 'plenty_id', 'operation_type' => 'Order Collection']
     * @return mixed Output depends on renderer type
     */
    public function renderSummary(MessageCollectorInterface $collector, array $options = []);

    /**
     * Render detailed messages
     *
     * @param MessageCollectorInterface $collector The message collector containing messages to render
     * @param array $options Rendering options. Available options:
     *                       - 'operation_type' (string): Operation type for messages title. Default: 'Export'
     * @return mixed Output depends on renderer type
     */
    public function renderDetails(MessageCollectorInterface $collector, array $options = []);

    /**
     * Check if renderer supports specific output type
     *
     * @param string $outputType
     * @return bool
     */
    public function supports(string $outputType): bool;
}