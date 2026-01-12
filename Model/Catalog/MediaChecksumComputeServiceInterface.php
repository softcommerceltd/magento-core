<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

/**
 * Interface MediaChecksumComputeServiceInterface
 * Used to pre-compute MD5 checksums for product media gallery images.
 */
interface MediaChecksumComputeServiceInterface
{
    /**
     * Get count of images without checksums
     *
     * @return int
     */
    public function getMissingChecksumCount(): int;

    /**
     * Get image value_ids with missing checksums
     *
     * @param int $limit
     * @param int $offset
     * @return array Array of [value_id, value, entity_id]
     */
    public function getImagesWithMissingChecksums(int $limit = 100, int $offset = 0): array;

    /**
     * Compute checksums for specific image value_ids
     *
     * @param array $valueIds
     * @param bool $force Recompute even if checksum exists
     * @return array ['computed' => int, 'skipped' => int, 'errors' => array]
     */
    public function computeForValueIds(array $valueIds, bool $force = false): array;

    /**
     * Compute checksums for all images of specific products
     *
     * @param array $productIds
     * @param bool $force Recompute even if checksum exists
     * @return array ['computed' => int, 'skipped' => int, 'errors' => array]
     */
    public function computeForProducts(array $productIds, bool $force = false): array;

    /**
     * Compute all missing checksums in batches
     *
     * @param int $batchSize
     * @param bool $force Recompute even if checksum exists
     * @param callable|null $progressCallback Called with (processed, total) after each batch
     * @return array ['computed' => int, 'skipped' => int, 'errors' => array]
     */
    public function computeAll(int $batchSize = 100, bool $force = false, ?callable $progressCallback = null): array;
}
