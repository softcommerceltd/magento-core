<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework;

use SoftCommerce\Core\Model\Source\StatusInterface;

/**
 * Interface MessageStorageInterface used to store
 * and retrieve messages in array format.
 */
interface MessageStorageInterface
{
    public const ENTITY = 'entity';
    public const STATUS = 'status';
    public const MESSAGE = 'message';

    /**
     * @param int|string|null $entity
     * @param array $status
     * @return array
     */
    public function getData($entity = null, array $status = []): array;

    /**
     * @param string $status
     * @return array
     */
    public function getDataByStatus(string $status): array;

    /**
     * @param string|array|mixed $message
     * @param int|string $entity
     * @param string $status
     * @return $this
     */
    public function addData($message, $entity, string $status = StatusInterface::SUCCESS): static;

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data): static;

    /**
     * @param array $data
     * @param int|string|null $key
     * @return $this
     */
    public function mergeData(array $data, $key = null): static;

    /**
     * @return array
     */
    public function getEntityIds(): array;

    /**
     * @param  int|string|null $key
     * @return $this
     */
    public function resetData($key = null): static;
}
