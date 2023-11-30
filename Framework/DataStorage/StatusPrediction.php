<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\DataStorage;

use SoftCommerce\Core\Model\Source\StatusInterface;

/**
 * @inheritDoc
 */
class StatusPrediction implements StatusPredictionInterface
{
    /**
     * @inheritDoc
     */
    public function execute(array $data, $fallback = StatusInterface::SUCCESS): string
    {
        if ((array_key_exists(StatusInterface::SUCCESS, $data) && array_key_exists(StatusInterface::ERROR, $data))
            || array_key_exists(StatusInterface::WARNING, $data)
        ) {
            $status = StatusInterface::WARNING;
        } elseif (array_key_exists(StatusInterface::CRITICAL, $data) || array_key_exists(StatusInterface::ERROR, $data)) {
            $status = StatusInterface::ERROR;
        } elseif (array_key_exists(StatusInterface::NOTICE, $data) && !array_key_exists(StatusInterface::SUCCESS, $data)) {
            $status = StatusInterface::NOTICE;
        } elseif (array_key_exists(StatusInterface::SKIPPED, $data)) {
            $status = StatusInterface::SKIPPED;
        } else {
            $status = $fallback;
        }
        return $status;
    }
}
