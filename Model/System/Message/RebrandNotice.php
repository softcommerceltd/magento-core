<?php
/**
 * Copyright © Byte8 Ltd (formerly Soft Commerce). All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\System\Message;

use Magento\Framework\Notification\MessageInterface;

class RebrandNotice implements MessageInterface
{
    public function getIdentity(): string
    {
        return md5('SOFTCOMMERCE_REBRAND_BYTE8');
    }

    public function isDisplayed(): bool
    {
        return true;
    }

    public function getText(): string
    {
        return (string) __(
            'Soft Commerce is now <strong>Byte8</strong>. Our modules, support, and services continue as before under the new name. For more information, visit <a href="%1" target="_blank">byte8.io</a>.',
            'https://byte8.io'
        );
    }

    public function getSeverity(): int
    {
        return self::SEVERITY_NOTICE;
    }
}
