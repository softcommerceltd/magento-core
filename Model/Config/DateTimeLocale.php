<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * @inheritDoc
 */
class DateTimeLocale implements DateTimeLocaleInterface
{
    /**
     * DateTimeLocale constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $dateTime
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly DateTime $dateTime,
        private readonly TimezoneInterface $timezone
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getGmtDateTime($input = null, ?string $format = \DateTime::W3C): ?string
    {
        return $this->dateTime->gmtDate($format, $input);
    }

    /**
     * @inheritDoc
     */
    public function getDateTime($date = null, string $format = 'Y-m-d H:i:s'): string
    {
        return $this->timezone->date($date)->format($format);
    }

    /**
     * @inheritDoc
     */
    public function setTimeZoneLocal($UTC = false)
    {
        if ($UTC) {
            return date_default_timezone_set($UTC);
        }

        return date_default_timezone_set(
            $this->scopeConfig->getValue('general/locale/timezone')
        );
    }
}
