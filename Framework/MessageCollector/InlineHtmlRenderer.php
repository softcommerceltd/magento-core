<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\MessageCollector;

use Magento\Framework\Phrase;

/**
 * Inline HTML renderer for MessageCollector message arrays
 *
 * Simple template-based renderer for converting message arrays to HTML.
 * Designed for order history comments, notifications, and inline displays.
 *
 * Unlike HtmlRenderer (which renders admin tables), this creates simple
 * inline HTML with custom templates and placeholder replacement.
 *
 * @example Basic usage
 * ```php
 * $renderer = $this->inlineHtmlRenderer;
 * $html = $renderer->render(
 *     $messages,
 *     '<i class="plenty status-{status}">{message}</i><br />'
 * );
 * ```
 *
 * @example With metadata
 * ```php
 * $html = $renderer->render(
 *     $messages,
 *     '<span class="msg-{status}">{message} [ID: {metadata.plenty_id}]</span><br />'
 * );
 * ```
 */
class InlineHtmlRenderer
{
    /**
     * Render messages array to HTML using a template
     *
     * Supported placeholders:
     * - {status} - Message status (success, error, warning, info)
     * - {message} - The message text (auto-escaped)
     * - {timestamp} - Unix timestamp
     * - {metadata.key} - Metadata field (e.g., {metadata.plenty_id})
     * - {metadata.entity_ids.magento_order} - Nested metadata
     *
     * @param array $messages Messages from MessageCollector::getEntityMessages()
     * @param string $template HTML template with placeholders
     * @param array $options Additional options:
     *                       - wrapper_start: HTML to prepend (default: '')
     *                       - wrapper_end: HTML to append (default: '')
     *                       - escape_html: Whether to escape message HTML (default: true)
     * @return string Rendered HTML
     */
    public function render(array $messages, string $template, array $options = []): string
    {
        $html = $options['wrapper_start'] ?? '';
        $escapeHtml = $options['escape_html'] ?? true;

        foreach ($messages as $item) {
            // Handle nested arrays recursively
            if (!isset($item['message']) || !isset($item['status'])) {
                if (is_array($item)) {
                    $html .= $this->render($item, $template, array_merge($options, [
                        'wrapper_start' => '',
                        'wrapper_end' => ''
                    ]));
                }
                continue;
            }

            $html .= $this->renderItem($item, $template, $escapeHtml);
        }

        $html .= $options['wrapper_end'] ?? '';

        return $html;
    }

    /**
     * Render a single message item
     *
     * @param array $item Message item with 'message', 'status', 'metadata', 'timestamp'
     * @param string $template HTML template
     * @param bool $escapeHtml Whether to escape message text
     * @return string Rendered HTML for this item
     */
    private function renderItem(array $item, string $template, bool $escapeHtml): string
    {
        $status = $item['status'] ?? 'info';
        $message = $this->parseMessage($item['message'], $escapeHtml);
        $metadata = $item['metadata'] ?? [];
        $timestamp = $item['timestamp'] ?? time();

        // Build replacement map
        $replacements = [
            '{status}' => $status,
            '{message}' => $message,
            '{timestamp}' => (string) $timestamp,
        ];

        // Add metadata replacements
        $replacements = array_merge($replacements, $this->buildMetadataReplacements($metadata, $escapeHtml));

        // Replace placeholders
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Build metadata replacements for template placeholders
     *
     * Supports nested metadata like: {metadata.entity_ids.magento_order}
     *
     * @param array $metadata Metadata array
     * @param bool $escapeHtml Whether to escape values
     * @param string $prefix Prefix for placeholder keys
     * @return array Replacement map ['{metadata.key}' => 'value']
     */
    private function buildMetadataReplacements(
        array $metadata,
        bool $escapeHtml,
        string $prefix = 'metadata'
    ): array {
        $replacements = [];

        foreach ($metadata as $key => $value) {
            $placeholder = '{' . $prefix . '.' . $key . '}';

            if (is_array($value)) {
                // Recursively handle nested arrays
                $replacements = array_merge(
                    $replacements,
                    $this->buildMetadataReplacements($value, $escapeHtml, $prefix . '.' . $key)
                );
                // Also provide JSON for the whole array
                $jsonValue = json_encode($value);
                $replacements[$placeholder] = $escapeHtml
                    ? htmlspecialchars($jsonValue, ENT_QUOTES, 'UTF-8')
                    : $jsonValue;
            } elseif ($value instanceof Phrase) {
                $rendered = $value->render();
                $replacements[$placeholder] = $escapeHtml
                    ? htmlspecialchars($rendered, ENT_QUOTES, 'UTF-8')
                    : $rendered;
            } elseif (is_scalar($value)) {
                $strValue = (string) $value;
                $replacements[$placeholder] = $escapeHtml
                    ? htmlspecialchars($strValue, ENT_QUOTES, 'UTF-8')
                    : $strValue;
            } else {
                // Objects or other types
                $strValue = (string) $value;
                $replacements[$placeholder] = $escapeHtml
                    ? htmlspecialchars($strValue, ENT_QUOTES, 'UTF-8')
                    : $strValue;
            }
        }

        return $replacements;
    }

    /**
     * Parse message to string
     *
     * @param mixed $message Message value (string, Phrase, array)
     * @param bool $escapeHtml Whether to escape HTML
     * @return string Parsed message
     */
    private function parseMessage($message, bool $escapeHtml): string
    {
        if ($message instanceof Phrase) {
            $message = $message->render();
        } elseif (is_array($message)) {
            $message = implode(' | ', array_map(function($item) use ($escapeHtml) {
                return $this->parseMessage($item, $escapeHtml);
            }, $message));
        } else {
            $message = (string) $message;
        }

        return $escapeHtml ? htmlspecialchars($message, ENT_QUOTES, 'UTF-8') : $message;
    }

    /**
     * Render messages with optional header and icon
     *
     * Convenience method that adds a formatted header with optional icon
     * before rendering messages using a template.
     *
     * @param array $messages Messages array
     * @param string|null $header Optional header (e.g., "Synchronisation [7608]")
     * @param string|null $icon Optional icon class (e.g., "fa-solid fa-upload")
     * @param string|null $itemTemplate Optional template for each message item
     *                                  Default: '<i class="message status-{status}">{message}</i><br />'
     * @return string Rendered HTML
     */
    public function renderWithHeader(
        array $messages,
        ?string $header = null,
        ?string $icon = null,
        ?string $itemTemplate = null
    ): string {
        // Use default template if not provided
        $template = $itemTemplate ?? '<i class="message status-{status}">{message}</i><br />';

        $html = '';

        if ($header) {
            $html .= '<b>' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</b>';
            if ($icon) {
                $html .= '&nbsp;<i class="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '"></i>';
            }
            $html .= '<br />';
        }

        $html .= $this->render($messages, $template);

        return $html;
    }
}
