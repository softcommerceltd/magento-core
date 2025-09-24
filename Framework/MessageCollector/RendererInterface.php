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
     * @param MessageCollectorInterface $collector
     * @param array $options Rendering options (verbosity, format, etc.)
     * @return mixed Output depends on renderer type
     */
    public function render(MessageCollectorInterface $collector, array $options = []);

    /**
     * Render summary only
     *
     * @param MessageCollectorInterface $collector
     * @param array $options
     * @return mixed
     */
    public function renderSummary(MessageCollectorInterface $collector, array $options = []);

    /**
     * Render detailed messages
     *
     * @param MessageCollectorInterface $collector
     * @param array $options
     * @return mixed
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