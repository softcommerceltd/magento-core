<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\MessageStorage;

/**
 * Interface SummaryRendererInterface
 * Provides methods for rendering message summaries from structured message data
 * @deprecated in favour of
 * @see \SoftCommerce\Core\Framework\MessageCollectorInterface
 */
interface SummaryRendererInterface
{
    /**
     * Render summary from messages
     *
     * @param array $messages
     * @return string
     */
    public function renderSummary(array $messages): string;

    /**
     * Render detailed summary with counts
     *
     * @param array $messages
     * @return array
     */
    public function renderDetailedSummary(array $messages): array;
}
