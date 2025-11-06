<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

use Magento\Framework\App\ResourceConnection;
use SoftCommerce\Core\Model\Eav\GetEntityTypeIdInterface;
use SoftCommerce\Core\Model\Trait\ConnectionTrait;
use SoftCommerce\Core\Model\Utils\GetEntityMetadataInterface;
use function array_diff;
use function array_merge;
use function array_keys;
use function array_unique;
use function explode;
use function strtolower;
use function trim;

/**
 * @inheritDoc
 */
class SkuStorage implements SkuStorageInterface
{
    use ConnectionTrait;

    /**
     * @var string[]
     */
    private array $attributes;

    /**
     * @var array
     */
    private array $skuData = [];

    /**
     * @var string[]
     */
    private array $excludedAttributes = [
        'has_options',
        'required_options'
    ];

    /**
     * @param GetEntityMetadataInterface $getEntityMetadata
     * @param GetEntityTypeIdInterface $getEntityTypeId
     * @param ResourceConnection $resourceConnection
     * @param array $attributes
     */
    public function __construct(
        private readonly GetEntityMetadataInterface $getEntityMetadata,
        private readonly GetEntityTypeIdInterface $getEntityTypeId,
        private readonly ResourceConnection $resourceConnection,
        array $attributes = []
    ) {
        $this->attributes = $attributes;
        if ($this->attributes) {
            $this->excludedAttributes = [];
            if (array_diff(['entity_id', 'sku'], $this->attributes)) {
                $this->attributes = array_unique(
                    array_merge($this->attributes, ['entity_id', 'sku'])
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getData(?string $sku = null, ?string $index = null, bool $reset = false)
    {
        if (!$this->skuData || false !== $reset) {
            $this->skuData = $this->getSkuData();
        }

        if (null === $sku) {
            return $this->skuData;
        }

        $sku = $this->parseSku($sku);
        return null !== $index
            ? ($this->skuData[$sku][$index] ?? null)
            : $this->skuData[$sku] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setData(string $sku, $data, ?string $index = null)
    {
        $sku = $this->parseSku($sku);
        null !== $index
            ? $this->skuData[$sku][$index] = $data
            : $this->skuData[$sku] = $data;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDataByEntityId(int $entityId, ?string $index = null)
    {
        $result = current(
            array_filter($this->getData(), function ($item) use ($entityId) {
                return isset($item['entity_id']) && $item['entity_id'] == $entityId;
            })
        ) ?: [];

        return null !== $index
            ? ($result[$index] ?? null)
            : $result;
    }

    /**
     * @inheritDoc
     */
    public function getDataByAttribute(string $attributeCode, string $value, ?string $index = null)
    {
        // First check if it's a direct column in catalog_product_entity
        $result = current(
            array_filter($this->getData(), function ($item) use ($attributeCode, $value) {
                return isset($item[$attributeCode])
                    && strtolower(trim($item[$attributeCode])) === strtolower(trim($value));
            })
        );

        if ($result) {
            return null !== $index ? ($result[$index] ?? null) : $result;
        }

        // If not found in direct columns, try EAV attribute lookup
        $sku = $this->getSkuByEavAttribute($attributeCode, $value);
        if ($sku) {
            return $this->getData($sku, $index);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function isSkuExists(string $sku): bool
    {
        return (bool) $this->getData($sku);
    }

    /**
     * @inheritDoc
     */
    public function isNewSku(string $sku): bool
    {
        $skuData = $this->getData($sku);
        return !$skuData || false !== ($skuData[self::IS_NEW_SKU] ?? false);
    }

    /**
     * @inheritDoc
     */
    public function isProcessedSku(string $sku): bool
    {
        return (bool) $this->getData($sku, self::IS_PROCESSED_SKU);
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getSkuData(): array
    {
        $catalogProductEntityTable = $this->getConnection()->getTableName('catalog_product_entity');
        if (!$this->attributes) {
            $this->attributes = array_keys($this->getConnection()->describeTable($catalogProductEntityTable));
            $this->attributes = array_diff($this->attributes, $this->excludedAttributes);
        }

        if ($this->getEntityMetadata->getLinkField() !== $this->getEntityMetadata->getIdentifierField()) {
            $this->attributes[] = $this->getEntityMetadata->getLinkField();
        }

        $select = $this->getConnection()->select()
            ->from(['main_tb' => $catalogProductEntityTable], $this->attributes)
            ->joinLeft(
                ['cpw_tb' => $this->getConnection()->getTableName('catalog_product_website')],
                'main_tb.entity_id = cpw_tb.product_id',
                [
                    'website_ids' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT cpw_tb.website_id)')
                ]
            )
            ->group('main_tb.entity_id');

        $result = [];
        foreach ($this->getConnection()->fetchAll($select) as $item) {
            $sku = $this->parseSku($item['sku'] ?? '');
            $item['website_ids'] = isset($item['website_ids']) ? explode(',', $item['website_ids']) : [];
            $item[self::IS_NEW_SKU] = false;
            $result[$sku] = $item;
        }

        return $result;
    }

    /**
     * Get product SKU by EAV attribute value
     *
     * Queries EAV tables (varchar and text) to find a product with the specified
     * attribute value, then returns its SKU.
     *
     * @param string $attributeCode
     * @param string $value
     * @return string|null Product SKU if found, null otherwise
     * @throws \Exception
     */
    private function getSkuByEavAttribute(string $attributeCode, string $value): ?string
    {
        $connection = $this->getConnection();

        // Get attribute ID
        $attributeId = $connection->fetchOne(
            $connection->select()
                ->from($connection->getTableName('eav_attribute'), ['attribute_id'])
                ->where('entity_type_id = ?', $this->getEntityTypeId->execute())
                ->where('attribute_code = ?', $attributeCode)
        );

        if (!$attributeId) {
            return null;
        }

        // Try varchar table first (most common for text attributes)
        foreach (['varchar', 'text'] as $type) {
            $attributeValueTable = $connection->getTableName('catalog_product_entity_' . $type);

            $select = $connection->select()
                ->from(['cpev' => $attributeValueTable], [])
                ->joinInner(
                    ['cpe' => $connection->getTableName('catalog_product_entity')],
                    'cpev.' . $this->getEntityMetadata->getLinkField() . ' = cpe.' . $this->getEntityMetadata->getLinkField(),
                    ['sku']
                )
                ->where('cpev.attribute_id = ?', $attributeId)
                ->where('cpev.value = ?', $value)
                ->limit(1);

            $sku = $connection->fetchOne($select);

            if ($sku) {
                return $this->parseSku($sku);
            }
        }

        return null;
    }

    /**
     * @param string $sku
     * @return string
     */
    private function parseSku(string $sku): string
    {
        return strtolower(trim($sku));
    }
}
