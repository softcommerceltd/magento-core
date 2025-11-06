<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Logger\Monolog;

/**
 * @inheritDoc
 */
class LogProcessor extends Monolog implements LogProcessorInterface
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        string $name,
        array $handlers = [],
        array $processors = []
    ) {
        $handlers = $this->filterHandlers($handlers);
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * @inheritDoc
     */
    public function execute(
        string $message,
        array $context = [],
        int $level = self::DEBUG,
        bool $printToArray = false
    ): void
    {
        /** @todo $printToArray - implement json formatPrettyPrint $printToArray */
        $this->addRecord($level, $message, $context);
    }

    /**
     * @param array $handlers
     * @return array
     */
    private function filterHandlers(array $handlers): array
    {
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_IS_ACTIVE_LOG_ROTATION)) {
            unset($handlers['rotation_debug']);
            return $handlers;
        }
        foreach ($handlers as $index => $handler) {
            $indexName = explode('_', $index);
            if ('rotation' === (string) current($indexName)) {
                $indexName = (string) next($indexName);
                $handlers[$indexName] = $handler;
                unset($handlers[$index]);
            }
        }

        return $handlers;
    }
}
