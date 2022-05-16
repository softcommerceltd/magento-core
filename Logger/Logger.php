<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Logger;

use Psr\Log\LoggerInterface;

/**
 * Class Logger
 * used to log data to file
 */
class Logger
{
    const DEBUG_KEYS_MASK = '****';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param $message
     * @param array $data
     */
    public function emergency($message, array $data = [])
    {
        $this->logger->emergency($message, $data);
    }

    /**
     * @param $message
     * @param array $data
     */
    public function alert($message, array $data = [])
    {
        $this->logger->alert($message, $data);
    }

    /**
     * @param $message
     * @param array $data
     */
    public function critical($message, array $data = [])
    {
        $this->logger->critical($message, $data);
    }

    /**
     * @param $message
     * @param array $data
     */
    public function error($message, array $data = [])
    {
        $this->logger->error($message, $data);
    }

    /**
     * @param $message
     * @param array $data
     */
    public function warning($message, array $data = [])
    {
        $this->logger->warning($message, $data);
    }

    /**
     * @param $message
     * @param array $data
     */
    public function notice($message, array $data = [])
    {
        $this->logger->notice($message, $data);
    }

    /**
     * @param $message
     * @param array $data
     */
    public function info($message, array $data = [])
    {
        $this->logger->info($message, $data);
    }

    /**
     * @param $message
     * @param $context
     * @param bool $printToArray
     */
    public function debug($message, $context, $printToArray = false)
    {
        if (false === $printToArray) {
            $this->logger->debug($message, $context);
        } else {
            $this->logger->debug(var_export($context, true), is_array($message) ? $message : [$message]);
        }
    }

    /**
     * @param $level
     * @param $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        $this->logger->log($level, $message, $context);
    }

    /**
     * Recursive filter data by private conventions
     *
     * @param array $debugData
     * @param array $debugReplacePrivateDataKeys
     * @return array
     */
    protected function filterDebugData(array $debugData, array $debugReplacePrivateDataKeys)
    {
        $debugReplacePrivateDataKeys = array_map('strtolower', $debugReplacePrivateDataKeys);

        foreach (array_keys($debugData) as $key) {
            if (in_array(strtolower($key), $debugReplacePrivateDataKeys)) {
                $debugData[$key] = self::DEBUG_KEYS_MASK;
            } elseif (is_array($debugData[$key])) {
                $debugData[$key] = $this->filterDebugData($debugData[$key], $debugReplacePrivateDataKeys);
            }
        }
        return $debugData;
    }
}
