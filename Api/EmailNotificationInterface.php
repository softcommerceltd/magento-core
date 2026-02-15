<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;

/**
 * Interface EmailNotificationInterface
 *
 * Service for sending email notifications using the global email log configuration.
 * Uses settings from Stores > Configuration > Soft Commerce > Core Configuration > Developer Settings.
 */
interface EmailNotificationInterface
{
    /**
     * Check if email notifications are enabled
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Send email notification
     *
     * @param string $subject Email subject line
     * @param string $content Main message content
     * @param array $additionalData Optional additional template variables
     * @return void
     * @throws LocalizedException
     * @throws MailException
     */
    public function send(string $subject, string $content, array $additionalData = []): void;

    /**
     * Get configured recipient email
     *
     * @return string|null
     */
    public function getRecipient(): ?string;
}
