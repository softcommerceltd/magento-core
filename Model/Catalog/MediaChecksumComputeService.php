<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use SoftCommerce\Core\Model\Utils\GetEntityMetadataInterface;

/**
 * @inheritDoc
 */
class MediaChecksumComputeService implements MediaChecksumComputeServiceInterface
{
    private const MEDIA_GALLERY_TABLE = 'catalog_product_entity_media_gallery';
    private const MEDIA_GALLERY_VALUE_TO_ENTITY_TABLE = 'catalog_product_entity_media_gallery_value_to_entity';

    /**
     * @param FileImageManagementInterface $fileImageManagement
     * @param GetEntityMetadataInterface $getEntityMetadata
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly FileImageManagementInterface $fileImageManagement,
        private readonly GetEntityMetadataInterface $getEntityMetadata,
        private readonly ResourceConnection $resourceConnection,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getMissingChecksumCount(): int
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                $connection->getTableName(self::MEDIA_GALLERY_TABLE),
                ['count' => new \Zend_Db_Expr('COUNT(*)')]
            )
            ->where('md5_checksum IS NULL OR md5_checksum = ?', '');

        return (int) $connection->fetchOne($select);
    }

    /**
     * @inheritDoc
     */
    public function getImagesWithMissingChecksums(int $limit = 100, int $offset = 0): array
    {
        $connection = $this->resourceConnection->getConnection();
        $linkField = $this->getEntityMetadata->getLinkField();

        $select = $connection->select()
            ->from(
                ['mg' => $connection->getTableName(self::MEDIA_GALLERY_TABLE)],
                ['value_id', 'value']
            )
            ->joinInner(
                ['mgvte' => $connection->getTableName(self::MEDIA_GALLERY_VALUE_TO_ENTITY_TABLE)],
                'mg.value_id = mgvte.value_id',
                ['entity_id' => "mgvte.$linkField"]
            )
            ->where('mg.md5_checksum IS NULL OR mg.md5_checksum = ?', '')
            ->order("mgvte.$linkField ASC")
            ->limit($limit, $offset);

        return $connection->fetchAll($select);
    }

    /**
     * @inheritDoc
     */
    public function computeForValueIds(array $valueIds, bool $force = false): array
    {
        if (empty($valueIds)) {
            return ['computed' => 0, 'skipped' => 0, 'errors' => []];
        }

        $connection = $this->resourceConnection->getConnection();
        $catalogProductDir = $this->fileImageManagement->getCatalogProductDirectory();

        // Fetch image data for value IDs
        $select = $connection->select()
            ->from(
                $connection->getTableName(self::MEDIA_GALLERY_TABLE),
                ['value_id', 'value', 'md5_checksum']
            )
            ->where('value_id IN (?)', $valueIds);

        $images = $connection->fetchAll($select);

        return $this->processImages($images, $catalogProductDir, $force);
    }

    /**
     * @inheritDoc
     */
    public function computeForProducts(array $productIds, bool $force = false): array
    {
        if (empty($productIds)) {
            return ['computed' => 0, 'skipped' => 0, 'errors' => []];
        }

        $connection = $this->resourceConnection->getConnection();
        $linkField = $this->getEntityMetadata->getLinkField();
        $catalogProductDir = $this->fileImageManagement->getCatalogProductDirectory();

        $select = $connection->select()
            ->from(
                ['mg' => $connection->getTableName(self::MEDIA_GALLERY_TABLE)],
                ['value_id', 'value', 'md5_checksum']
            )
            ->joinInner(
                ['mgvte' => $connection->getTableName(self::MEDIA_GALLERY_VALUE_TO_ENTITY_TABLE)],
                'mg.value_id = mgvte.value_id',
                []
            )
            ->where("mgvte.$linkField IN (?)", $productIds);

        if (!$force) {
            $select->where('mg.md5_checksum IS NULL OR mg.md5_checksum = ?', '');
        }

        $images = $connection->fetchAll($select);

        return $this->processImages($images, $catalogProductDir, $force);
    }

    /**
     * @inheritDoc
     */
    public function computeAll(int $batchSize = 100, bool $force = false, ?callable $progressCallback = null): array
    {
        $connection = $this->resourceConnection->getConnection();
        $catalogProductDir = $this->fileImageManagement->getCatalogProductDirectory();

        // Get total count
        if ($force) {
            $select = $connection->select()
                ->from(
                    $connection->getTableName(self::MEDIA_GALLERY_TABLE),
                    ['count' => new \Zend_Db_Expr('COUNT(*)')]
                );
            $totalCount = (int) $connection->fetchOne($select);
        } else {
            $totalCount = $this->getMissingChecksumCount();
        }

        if ($totalCount === 0) {
            return ['computed' => 0, 'skipped' => 0, 'errors' => [], 'total' => 0];
        }

        $result = ['computed' => 0, 'skipped' => 0, 'errors' => [], 'total' => $totalCount];
        $offset = 0;

        while (true) {
            $select = $connection->select()
                ->from(
                    $connection->getTableName(self::MEDIA_GALLERY_TABLE),
                    ['value_id', 'value', 'md5_checksum']
                )
                ->limit($batchSize, $offset);

            if (!$force) {
                $select->where('md5_checksum IS NULL OR md5_checksum = ?', '');
            }

            $images = $connection->fetchAll($select);

            if (empty($images)) {
                break;
            }

            $batchResult = $this->processImages($images, $catalogProductDir, $force);

            $result['computed'] += $batchResult['computed'];
            $result['skipped'] += $batchResult['skipped'];
            $result['errors'] = array_merge($result['errors'], $batchResult['errors']);

            $processed = $result['computed'] + $result['skipped'] + count($result['errors']);

            if ($progressCallback) {
                $progressCallback($processed, $totalCount);
            }

            // If not forcing and we got less than batch size, we're done
            if (!$force && count($images) < $batchSize) {
                break;
            }

            $offset += $batchSize;

            // Prevent infinite loop for force mode
            if ($offset >= $totalCount) {
                break;
            }
        }

        return $result;
    }

    /**
     * Process images and compute checksums
     *
     * @param array $images
     * @param string $catalogProductDir
     * @param bool $force
     * @return array
     */
    private function processImages(array $images, string $catalogProductDir, bool $force): array
    {
        $result = ['computed' => 0, 'skipped' => 0, 'errors' => []];
        $updateData = [];

        foreach ($images as $image) {
            $valueId = $image['value_id'] ?? null;
            $value = $image['value'] ?? null;
            $existingChecksum = $image['md5_checksum'] ?? null;

            if (!$valueId || !$value) {
                continue;
            }

            // Skip if checksum exists and not forcing
            if (!$force && !empty($existingChecksum)) {
                $result['skipped']++;
                continue;
            }

            $filePath = ltrim($value, '/');
            $fullPath = $this->fileImageManagement->getImageFile(
                $filePath,
                $catalogProductDir
            );

            if (!$fullPath) {
                $result['errors'][] = [
                    'value_id' => $valueId,
                    'value' => $value,
                    'error' => 'File not found or not readable'
                ];
                $this->logger->warning(
                    sprintf('Media checksum compute: File not found - %s', $value)
                );
                continue;
            }

            try {
                $checksum = md5_file($fullPath);

                if ($checksum === false) {
                    $result['errors'][] = [
                        'value_id' => $valueId,
                        'value' => $value,
                        'error' => 'Failed to compute MD5 checksum'
                    ];
                    continue;
                }

                $updateData[] = [
                    'value_id' => $valueId,
                    'md5_checksum' => $checksum
                ];

                $result['computed']++;

            } catch (\Exception $e) {
                $result['errors'][] = [
                    'value_id' => $valueId,
                    'value' => $value,
                    'error' => $e->getMessage()
                ];
                $this->logger->error(
                    sprintf('Media checksum compute error for %s: %s', $value, $e->getMessage())
                );
            }
        }

        // Batch update checksums
        if (!empty($updateData)) {
            $this->saveChecksums($updateData);
        }

        return $result;
    }

    /**
     * Save checksums to database
     *
     * @param array $data
     * @return void
     */
    private function saveChecksums(array $data): void
    {
        if (empty($data)) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();

        $connection->insertOnDuplicate(
            $connection->getTableName(self::MEDIA_GALLERY_TABLE),
            $data,
            ['md5_checksum']
        );
    }
}
