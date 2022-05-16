<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Config;

/**
 * Interface DeveloperInterface used to retrieve
 * developer system configuration data.
 */
interface DeveloperInterface
{
    const XML_PATH_IS_ACTIVE_LOG = '/dev/debug_enabled';
    const XML_PATH_SHOULD_LOG_PRINT_TO_ARRAY = '/dev/debug_print_to_array';
    const XML_PATH_LOG_LEVEL = '/dev/debug_level';
    const XML_PATH_IS_ACTIVE_MAIL_LOG = '/dev/is_active_mail_log';
    const XML_PATH_MAIL_LOG_RECIPIENT = '/dev/mail_log_email';
    const XML_PATH_MAIL_LOG_EMAIL_IDENTITY = '/dev/mail_log_email_identity';
    const XML_PATH_MAIL_LOG_EMAIL_TEMPLATE = '/dev/mail_log_email_template';

    /**
     * @return bool
     */
    public function isActiveLog(): bool;

    /**
     * @return bool
     */
    public function shouldLogPrintToArray(): bool;

    /**
     * @return array|string[]
     */
    public function getLogLevel(): array;

    /**
     * @return bool
     */
    public function isActiveEmailLog(): bool;

    /**
     * @return string|null
     */
    public function getMailLogRecipient(): ?string;

    /**
     * @return string|null
     */
    public function getMailLogEmailIdentity(): ?string;

    /**
     * @return string|null
     */
    public function getMailLogEmailTemplate(): ?string;
}
