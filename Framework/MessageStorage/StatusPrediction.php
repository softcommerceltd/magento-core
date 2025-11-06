<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\MessageStorage;

use SoftCommerce\Core\Framework\MessageStorageInterface;
use SoftCommerce\Core\Model\Source\StatusInterface;
use function array_column;
use function array_merge;
use function in_array;
use function is_array;

/**
 * @inheritDoc
 * @deprecated in favour of
 * @see \SoftCommerce\Core\Framework\MessageCollectorInterface
 */
class StatusPrediction implements StatusPredictionInterface
{
    /**
     * @inheritDoc
     */
    public function execute(array $data, string $fallback = StatusInterface::SUCCESS): string
    {
        if (!$statuses = $this->getStatuses($data)) {
            return $fallback;
        }

        if ((in_array(StatusInterface::SUCCESS, $statuses) && in_array(StatusInterface::ERROR, $statuses))
            || in_array(StatusInterface::WARNING, $statuses)
        ) {
            $status = StatusInterface::WARNING;
        } elseif (in_array(StatusInterface::CRITICAL, $statuses) || in_array(StatusInterface::ERROR, $statuses)) {
            $status = StatusInterface::ERROR;
        } elseif (in_array(StatusInterface::NOTICE, $statuses) && !in_array(StatusInterface::SUCCESS, $statuses)) {
            $status = StatusInterface::NOTICE;
        } elseif (in_array(StatusInterface::SKIPPED, $statuses)
            && !in_array(StatusInterface::ERROR, $statuses)
            && !in_array(StatusInterface::SUCCESS, $statuses)
        ) {
            $status = StatusInterface::SKIPPED;
        } else {
            $status = $fallback;
        }
        return $status;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getStatuses(array $data): array
    {
        if (isset($data[MessageStorageInterface::STATUS])) {
            return [$data[MessageStorageInterface::STATUS]];
        }

        if ($statuses = array_column($data, MessageStorageInterface::STATUS)) {
            return $statuses;
        }

        $statuses = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            $statuses = array_merge(
                $statuses,
                array_column($item, MessageStorageInterface::STATUS)
            );
        }

        return $statuses;
    }
}
