<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use SoftCommerce\Core\Model\Eav\GetEntityTypeIdInterface;
use SoftCommerce\Core\Model\Utils\GetEntityMetadataInterface;
use function array_keys;
use function array_merge;
use function is_array;
use function md5_file;

/**
 * @inheritDoc
 */
class MediaManagement implements MediaManagementInterface
{
    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    /**
     * @var FileImageManagementInterface
     */
    private FileImageManagementInterface $fileImageManagement;

    /**
     * @var GetEntityMetadataInterface
     */
    private GetEntityMetadataInterface $getEntityMetadata;

    /**
     * @var GetEntityTypeIdInterface
     */
    private GetEntityTypeIdInterface $getEntityTypeId;

    /**
     * @var string[]|null
     */
    private ?array $imageTypes = null;

    /**
     * @var string[]|null
     */
    private ?array $imageTypeLabels = null;

    /**
     * @var int|null
     */
    private ?int $mediaGalleryAttributeId = null;

    /**
     * @var array
     */
    private array $mediaGalleryData = [];

    /**
     * @var array
     */
    private array $mediaVideoGalleryData = [];

    /**
     * @param FileImageManagementInterface $fileImageManagement
     * @param GetEntityMetadataInterface $getEntityMetadata
     * @param GetEntityTypeIdInterface $getEntityTypeId
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        FileImageManagementInterface $fileImageManagement,
        GetEntityMetadataInterface $getEntityMetadata,
        GetEntityTypeIdInterface $getEntityTypeId,
        ResourceConnection $resourceConnection
    ) {
        $this->fileImageManagement = $fileImageManagement;
        $this->getEntityMetadata = $getEntityMetadata;
        $this->getEntityTypeId = $getEntityTypeId;
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * @inheritDoc
     */
    public function getMediaGalleryAttributeId(): int
    {
        if (null === $this->mediaGalleryAttributeId) {
            $select = $this->connection->select()
                ->from(
                    $this->connection->getTableName('eav_attribute'),
                    ['attribute_id']
                )
                ->where('attribute_code = ?', 'media_gallery')
                ->where('entity_type_id = ?', $this->getEntityTypeId->execute());

            $this->mediaGalleryAttributeId = (int) $this->connection->fetchOne($select);
        }
        return $this->mediaGalleryAttributeId;
    }

    /**
     * @inheritDoc
     */
    public function getImageTypes(): array
    {
        if (null === $this->imageTypes) {
            $select = $this->connection->select()
                ->from(
                    $this->connection->getTableName('eav_attribute'),
                    [
                        'attribute_id',
                        'attribute_code'
                    ]
                )
                ->where('frontend_input = ?', 'media_image');

            $this->imageTypes = $this->connection->fetchPairs($select) ?: [];
        }

        return $this->imageTypes;
    }

    /**
     * @inheritDoc
     */
    public function getImageTypesLabels(): array
    {
        if (null === $this->imageTypeLabels) {
            $attributeCodes = [];
            foreach ($this->getImageTypes() as $imageType) {
                $attributeCodes[] = "{$imageType}_label";
            }
            $select = $this->connection->select()
                ->from(
                    $this->connection->getTableName('eav_attribute'),
                    [
                        'attribute_id',
                        'attribute_code'
                    ]
                )
                ->where('attribute_code IN (?)', $attributeCodes);

            $this->imageTypeLabels = $this->connection->fetchPairs($select) ?: [];
        }

        return $this->imageTypeLabels;
    }

    /**
     * @inheritDoc
     */
    public function getMediaGallery(int $productId, ?string $md5Checksum = null): array
    {
        if (isset($this->mediaGalleryData[$productId])) {
            return null !== $md5Checksum
               ? ($this->mediaGalleryData[$productId][$md5Checksum] ?? [])
               : $this->mediaGalleryData[$productId];
        }

        $this->mediaGalleryData[$productId] = [];
        $linkFieldName = $this->getEntityMetadata->getLinkField();
        $select = $this->connection->select()
            ->from(
                ['mg' => $this->connection->getTableName('catalog_product_entity_media_gallery')],
                ['mg.value_id', 'mg.value', 'mg.media_type', 'mg.disabled', 'mg.md5_checksum']
            )
            ->joinInner(
                ['mgvte' => $this->connection->getTableName('catalog_product_entity_media_gallery_value_to_entity')],
                '(mg.value_id = mgvte.value_id)',
                null
            )
            ->where("mgvte.$linkFieldName = ?", $productId);

        $mediaGalleryValueTableName = $this->connection->getTableName('catalog_product_entity_media_gallery_value');
        foreach ($this->connection->fetchAll($select) as $item) {
            $value = $item['value'] ?? null;
            if (!$value || !$valueId = $item['value_id'] ?? null) {
                continue;
            }

            if (!$imageMd5Checksum = $item['md5_checksum'] ?? null) {
                $filePath = ltrim($value, '/');
                if (!$filePath = $this->fileImageManagement->getImageFile(
                    $filePath,
                    $this->fileImageManagement->getCatalogProductDirectory()
                )) {
                    continue;
                }

                $imageMd5Checksum = md5_file($filePath);
                $item['md5_checksum'] = null;
            }

            if (!$imageMd5Checksum) {
                continue;
            }

            $select = $this->connection->select()
                ->from($mediaGalleryValueTableName)
                ->where('value_id = ?', $valueId);
            $item['values'] = $this->connection->fetchAll($select) ?: [];

            $this->mediaGalleryData[$productId][$imageMd5Checksum] = $item;
        }

        return null !== $md5Checksum
            ? ($this->mediaGalleryData[$productId][$md5Checksum] ?? [])
            : $this->mediaGalleryData[$productId];
    }

    /**
     * @inheritDoc
     */
    public function getMediaVideoGallery(string $videoUrl, ?int $productId = null): array
    {
        if (isset($this->mediaVideoGalleryData[$videoUrl])) {
            return null !== $productId
                ? ($this->mediaVideoGalleryData[$videoUrl][$productId] ?? [])
                : $this->mediaVideoGalleryData[$videoUrl];
        }

        $this->mediaVideoGalleryData[$videoUrl] = [];
        $linkFieldName = $this->getEntityMetadata->getLinkField();
        $select = $this->connection->select()
            ->from(
                ['mgvv' => $this->connection->getTableName('catalog_product_entity_media_gallery_value_video')],
                ['mgvv.value_id', 'mgvv.provider', 'mgvv.url', 'mgvv.title', 'mgvv.description']
            )
            ->joinLeft(
                ['mg' => $this->connection->getTableName('catalog_product_entity_media_gallery')],
                'mgvv.value_id = mg.value_id AND mg.media_type = \'external-video\'',
                ['mg.value', 'mg.disabled', 'mg.md5_checksum']
            )
            ->joinLeft(
                ['mgvte' => $this->connection->getTableName('catalog_product_entity_media_gallery_value_to_entity')],
                '(mgvv.value_id = mgvte.value_id)',
                ["mgvte.$linkFieldName"]
            )
            ->where('mgvv.url = ?', $videoUrl);

        foreach ($this->connection->fetchAll($select) as $item) {
            $entityId = $item[$linkFieldName] ?? null;
            if (!$entityId || !$value = $item['value'] ?? null) {
                continue;
            }

            if (!$imageMd5Checksum = $item['md5_checksum'] ?? null) {
                $filePath = ltrim($value, '/');
                if (!$filePath = $this->fileImageManagement->getImageFile(
                    $filePath,
                    $this->fileImageManagement->getCatalogProductDirectory()
                )) {
                    continue;
                }

                $imageMd5Checksum = md5_file($filePath);
                $item['md5_checksum'] = null;
            }

            if (!$imageMd5Checksum) {
                continue;
            }

            $this->mediaVideoGalleryData[$videoUrl][$entityId][$imageMd5Checksum] = $item;
        }

        return null !== $productId
            ? ($this->mediaVideoGalleryData[$videoUrl][$productId] ?? [])
            : $this->mediaVideoGalleryData[$videoUrl];
    }

    /**
     * @inheritDoc
     */
    public function getLastMediaPosition(int $productId): int
    {
        $data = array_column($this->getMediaGallery($productId), 'values');
        $data = array_merge(...$data);

        $position = 0;
        if ($positionData = array_column($data, 'position')) {
            $position = max($positionData) + 1;
        }

        return $position;
    }

    /**
     * @inheritDoc
     */
    public function deleteMediaGallery($valueId): int
    {
        return (int) $this->connection->delete(
            $this->connection->getTableName('catalog_product_entity_media_gallery'),
            [
                'value_id IN (?)' => is_array($valueId) ? $valueId : [$valueId]
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteMediaGalleryValue($valueId, string $fieldId = 'record_id'): int
    {
        return (int) $this->connection->delete(
            $this->connection->getTableName('catalog_product_entity_media_gallery_value'),
            [
                "$fieldId IN (?)" => is_array($valueId) ? $valueId : [$valueId]
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteMediaGalleryImage($entityId, array $value = []): int
    {
        return (int) $this->connection->delete(
            $this->connection->getTableName('catalog_product_entity_varchar'),
            array_merge(
                [
                    $this->getEntityMetadata->getLinkField() . ' = ?' => $entityId,
                    'attribute_id IN (?)' => array_keys($this->getImageTypes())
                ],
                $value ? ['value IN (?)' => $value] : []
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteMediaGalleryImageLabel(int $entityId)
    {
        return (int) $this->connection->delete(
            $this->connection->getTableName('catalog_product_entity_varchar'),
            [
                $this->getEntityMetadata->getLinkField() . ' = ?' => $entityId,
                'attribute_id IN (?)' => array_keys($this->getImageTypesLabels())
            ]
        );
    }
}
