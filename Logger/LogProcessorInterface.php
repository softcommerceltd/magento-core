<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Logger;

/**
 * Interface LogProcessorInterface
 * Used to process logging.
 */
interface LogProcessorInterface
{
    public const XML_PATH_IS_ACTIVE_LOG_ROTATION = 'softcommerce_core/dev/is_active_log_rotation';

    /**
     * @param string $message
     * @param array $context
     * @param int $level
     * @param bool $printToArray
     */
    public function execute(
        string $message,
        array $context = [],
        int $level = \Monolog\Logger::DEBUG,
        bool $printToArray = false
    ): void;
}
