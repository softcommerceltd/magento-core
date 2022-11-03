<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @inheritDoc
 */
class Developer implements DeveloperInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $entity;

    /**
     * Developer constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param string $entityType
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        string $entityType = 'softcommerce_core'
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->entity = $entityType;
    }

    /**
     * @return bool
     */
    public function isActiveLog(): bool
    {
        return (bool) $this->scopeConfig->getValue($this->entity . self::XML_PATH_IS_ACTIVE_LOG);
    }

    /**
     * @return bool
     */
    public function shouldLogPrintToArray(): bool
    {
        return (bool) $this->scopeConfig->getValue($this->entity . self::XML_PATH_SHOULD_LOG_PRINT_TO_ARRAY);
    }

    /**
     * @return array|string[]
     */
    public function getLogLevel(): array
    {
        return explode(
            ',',
            $this->scopeConfig->getValue($this->entity . self::XML_PATH_LOG_LEVEL) ?: ''
        ) ?: [];
    }

    /**
     * @return bool
     */
    public function isActiveEmailLog(): bool
    {
        return (bool) $this->scopeConfig->getValue($this->entity . self::XML_PATH_IS_ACTIVE_MAIL_LOG);
    }

    /**
     * @return string|null
     */
    public function getMailLogRecipient(): ?string
    {
        return $this->scopeConfig->getValue($this->entity . self::XML_PATH_MAIL_LOG_RECIPIENT);
    }

    /**
     * @return string|null
     */
    public function getMailLogEmailIdentity(): ?string
    {
        return $this->scopeConfig->getValue($this->entity . self::XML_PATH_MAIL_LOG_EMAIL_IDENTITY);
    }

    /**
     * @return string|null
     */
    public function getMailLogEmailTemplate(): ?string
    {
        return $this->scopeConfig->getValue($this->entity . self::XML_PATH_MAIL_LOG_EMAIL_TEMPLATE);
    }
}
