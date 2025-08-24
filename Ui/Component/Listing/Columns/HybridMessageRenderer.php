<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Ui\Component\Listing\Columns;

use Magento\Framework\Escaper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use SoftCommerce\Core\Framework\DataStorage\StatusPredictionInterface;

/**
 * Generic hybrid message renderer with icon display, tooltip on hover, and modal on click
 */
class HybridMessageRenderer extends Column
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var StatusPredictionInterface
     */
    private StatusPredictionInterface $statusPrediction;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @param SerializerInterface $serializer
     * @param StatusPredictionInterface $statusPrediction
     * @param Escaper $escaper
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        SerializerInterface $serializer,
        StatusPredictionInterface $statusPrediction,
        Escaper $escaper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->statusPrediction = $statusPrediction;
        $this->escaper = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        $componentIndex = $this->getData('name');
        foreach ($dataSource['data']['items'] ?? [] as $index => $item) {
            if (!$data = $item[$componentIndex] ?? null) {
                continue;
            }

            $messages = $this->parseMessages($data);

            // Generate tooltip content
            $dataSource['data']['items'][$index][$componentIndex . '_tooltip'] = $this->generateTooltip($messages);

            // Generate modal content
            $dataSource['data']['items'][$index][$componentIndex . '_modal'] = $this->generateModalContent($messages);

            // Determine overall status for icon coloring
            $dataSource['data']['items'][$index][$componentIndex . '_status'] = $this->determineOverallStatus($messages);

            // Set flag for whether messages exist
            $dataSource['data']['items'][$index][$componentIndex . '_has_messages'] = !empty($messages);

            // Keep original for backward compatibility
            $dataSource['data']['items'][$index][$componentIndex] = $data;
        }

        return $dataSource;
    }

    /**
     * Parse messages from stored data
     *
     * @param mixed $data
     * @return array
     */
    protected function parseMessages($data): array
    {
        try {
            $messages = $this->serializer->unserialize($data);
            if (!is_array($messages)) {
                $messages = [$messages];
            }
        } catch (\InvalidArgumentException $e) {
            $messages = [$data];
        }

        return $this->normalizeMessages($messages);
    }

    /**
     * Normalize messages to consistent format
     *
     * @param array $messages
     * @return array
     */
    protected function normalizeMessages(array $messages): array
    {
        $normalized = [];

        foreach ($messages as $message) {
            if (is_array($message) && isset($message['message'])) {
                $normalized[] = [
                    'status' => $message['status'] ?? 'info',
                    'message' => $message['message'] ?? '',
                    'entity' => $message['entity'] ?? null
                ];
            } elseif (is_string($message)) {
                $normalized[] = [
                    'status' => 'info',
                    'message' => $message,
                    'entity' => null
                ];
            }
        }

        return $normalized;
    }

    /**
     * Generate tooltip content
     *
     * @param array $messages
     * @return string
     */
    protected function generateTooltip(array $messages): string
    {
        if (empty($messages)) {
            return '';
        }

        $lines = [];
        $grouped = $this->groupMessages($messages);

        foreach ($grouped as $status => $msgs) {
            $icon = $this->getStatusIcon($status);
            foreach ($msgs as $msg) {
                $text = $this->extractKeyInfo($msg['message']);
                $lines[] = $icon . ' ' . $this->escaper->escapeHtml($text);
            }
        }

        return implode('<br>', array_slice($lines, 0, 5)) .
               (count($lines) > 5 ? '<br>... ' . (count($lines) - 5) . ' more' : '');
    }

    /**
     * Generate modal content (detailed view)
     *
     * @param array $messages
     * @return string
     */
    protected function generateModalContent(array $messages): string
    {
        if (empty($messages)) {
            return '<div class="message-modal-empty">No messages available</div>';
        }

        $html = '<div class="hybrid-message-modal">';
        $grouped = $this->groupMessages($messages);

        foreach ($grouped as $status => $msgs) {
            $html .= '<div class="message-group status-' . $status . '">';
            $html .= '<h4>' . $this->escaper->escapeHtml(ucfirst($status)) . ' Messages</h4>';
            $html .= '<ul>';

            foreach ($msgs as $msg) {
                $icon = $this->getStatusIcon($status);
                $html .= '<li>';
                $html .= $icon . ' ' . $this->escaper->escapeHtml($msg['message']);
                $html .= '</li>';
            }

            $html .= '</ul>';
            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Group messages by status
     *
     * @param array $messages
     * @return array
     */
    protected function groupMessages(array $messages): array
    {
        $grouped = [];

        foreach ($messages as $msg) {
            $status = $msg['status'] ?? 'info';
            if (!isset($grouped[$status])) {
                $grouped[$status] = [];
            }
            $grouped[$status][] = $msg;
        }

        // Sort by priority: error, warning, notice, success, info
        $priority = ['error' => 0, 'warning' => 1, 'notice' => 2, 'success' => 3, 'info' => 4];
        uksort($grouped, function($a, $b) use ($priority) {
            return ($priority[$a] ?? 5) <=> ($priority[$b] ?? 5);
        });

        return $grouped;
    }

    /**
     * Extract key information from message
     *
     * @param string $message
     * @return string
     */
    protected function extractKeyInfo(string $message): string
    {
        // For short messages, preserve the type identifier but shorten if needed
        if (preg_match('/^(Stock\s+(physical|net|reservation))\s+(.+)$/i', $message, $matches)) {
            $prefix = $matches[1];
            $content = $matches[3];

            // Calculate available space for content after prefix
            $maxLength = 80;
            $prefixLength = strlen($prefix) + 2; // +2 for ": "
            $availableLength = $maxLength - $prefixLength;

            if (strlen($content) > $availableLength) {
                $content = substr($content, 0, $availableLength - 3) . '...';
            }

            return $prefix . ': ' . $content;
        }

        // For other messages, limit length
        if (strlen($message) > 80) {
            return substr($message, 0, 77) . '...';
        }

        return $message;
    }

    /**
     * Get status icon
     *
     * @param string $status
     * @return string
     */
    protected function getStatusIcon(string $status): string
    {
        return match ($status) {
            'success' => '<span class="icon-success">✓</span>',
            'error' => '<span class="icon-error">✕</span>',
            'warning' => '<span class="icon-warning">⚠</span>',
            'notice' => '<span class="icon-notice">ⓘ</span>',
            default => '<span class="icon-info">•</span>'
        };
    }

    /**
     * Determine overall status from messages
     *
     * @param array $messages
     * @return string
     */
    protected function determineOverallStatus(array $messages): string
    {
        $statuses = array_column($messages, 'status');

        // Priority order: error > warning > notice > success > info
        if (in_array('error', $statuses)) {
            return 'error';
        }
        if (in_array('warning', $statuses)) {
            return 'warning';
        }
        if (in_array('notice', $statuses)) {
            return 'notice';
        }
        if (in_array('success', $statuses)) {
            return 'success';
        }

        return 'info';
    }
}
