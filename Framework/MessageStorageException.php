<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * @inheritDoc
 */
class MessageStorageException extends LocalizedException
{
    /**
     * @param Phrase $phrase
     * @param int|string|null $entity
     * @param array $messages
     * @param \Exception|null $cause
     * @param int $code
     */
    public function __construct(
        Phrase $phrase,
        private mixed $entity = null,
        private array $messages = [],
        ?\Exception $cause = null,
        int $code = 0
    ) {
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * @return int|string|null
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
