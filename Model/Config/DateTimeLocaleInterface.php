<?php
/**
 * Copyright © Byte8 Ltd (formerly Soft Commerce). All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Config;

/**
 * Interface DateTimeLocaleInterface used to provide
 * date conversion.
 */
interface DateTimeLocaleInterface
{
    public const XML_PATH_GENERAL_LOCALE_TIMEZONE = 'general/locale/timezone';

    /**
     * @param null $input
     * @param string|null $format
     * @return string|null
     */
    public function getGmtDateTime(mixed $input = null, ?string $format = \DateTime::W3C): ?string;

    /**
     * @param mixed|null $date
     * @param string $format
     * @return string
     */
    public function getDateTime(mixed $date = null, string $format = 'Y-m-d H:i:s'): string;

    /**
     * @param false $UTC
     * @return bool
     */
    public function setTimeZoneLocal(mixed $UTC = false);
}
