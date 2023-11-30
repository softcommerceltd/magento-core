<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

/**
 * Interface MediaManagementInterface
 * used to manage media entity data.
 */
interface MediaManagementInterface
{
    /**
     * @return int
     */
    public function getMediaGalleryAttributeId(): int;

    /**
     * @return array
     */
    public function getImageTypes(): array;

    /**
     * @return array
     */
    public function getImageTypesLabels(): array;

    /**
     * @param int $productId
     * @param string|null $md5Checksum
     * @return array
     * @throws \Exception
     */
    public function getMediaGallery(int $productId, ?string $md5Checksum = null): array;

    /**
     * @param string $videoUrl
     * @param int|null $productId
     * @return array
     * @throws \Exception
     */
    public function getMediaVideoGallery(string $videoUrl, ?int $productId = null): array;

    /**
     * @param int $productId
     * @return int
     * @throws \Exception
     */
    public function getLastMediaPosition(int $productId): int;

    /**
     * @param int|int[] $valueId
     * @return int
     */
    public function deleteMediaGallery($valueId): int;

    /**
     * @param int|int[] $valueId
     * @param string $fieldId
     * @return int
     */
    public function deleteMediaGalleryValue($valueId, string $fieldId = 'record_id'): int;

    /**
     * @param $entityId
     * @param array $value
     * @return int
     */
    public function deleteMediaGalleryImage($entityId, array $value = []): int;

    /**
     * @param int $entityId
     * @return int
     */
    public function deleteMediaGalleryImageLabel(int $entityId);
}
