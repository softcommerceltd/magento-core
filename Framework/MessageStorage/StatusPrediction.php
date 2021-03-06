<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\MessageStorage;

use SoftCommerce\Core\Framework\MessageStorageInterface;
use SoftCommerce\Core\Model\Source\Status;
use function array_column;
use function array_merge;
use function in_array;
use function is_array;

/**
 * @inheritDoc
 */
class StatusPrediction implements StatusPredictionInterface
{
    /**
     * @inheritDoc
     */
    public function execute(array $data, string $fallback = Status::SUCCESS): string
    {
        if (!$statuses = $this->getStatuses($data)) {
            return $fallback;
        }

        if ((in_array(Status::SUCCESS, $statuses) && in_array(Status::ERROR, $statuses))
            || in_array(Status::WARNING, $statuses)
        ) {
            $status = Status::WARNING;
        } elseif (in_array(Status::CRITICAL, $statuses) || in_array(Status::ERROR, $statuses)) {
            $status = Status::ERROR;
        } elseif (in_array(Status::NOTICE, $statuses) && !in_array(Status::SUCCESS, $statuses)) {
            $status = Status::NOTICE;
        } elseif (in_array(Status::SKIPPED, $statuses) && !in_array(Status::ERROR, $statuses)) {
            $status = Status::SKIPPED;
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
