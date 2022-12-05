<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Utils;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
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
    /**
     * @var string[]
     */
    private array $attributes;

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    /**
     * @var GetEntityMetadataInterface
     */
    private GetEntityMetadataInterface $getEntityMetadata;

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
     * @param ResourceConnection $resourceConnection
     * @param array $attributes
     */
    public function __construct(
        GetEntityMetadataInterface $getEntityMetadata,
        ResourceConnection $resourceConnection,
        array $attributes = []
    ) {
        $this->getEntityMetadata = $getEntityMetadata;
        $this->connection = $resourceConnection->getConnection();
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
        $catalogProductEntityTable = $this->connection->getTableName('catalog_product_entity');
        if (!$this->attributes) {
            $this->attributes = array_keys($this->connection->describeTable($catalogProductEntityTable));
            $this->attributes = array_diff($this->attributes, $this->excludedAttributes);
        }

        if ($this->getEntityMetadata->getLinkField() !== $this->getEntityMetadata->getIdentifierField()) {
            $this->attributes[] = $this->getEntityMetadata->getLinkField();
        }

        $select = $this->connection->select()
            ->from(['main_tb' => $catalogProductEntityTable], $this->attributes)
            ->joinLeft(
                ['cpw_tb' => $this->connection->getTableName('catalog_product_website')],
                'main_tb.entity_id = cpw_tb.product_id',
                [
                    'website_ids' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT cpw_tb.website_id)')
                ]
            )
            ->group('main_tb.entity_id');

        $result = [];
        foreach ($this->connection->fetchAll($select) as $item) {
            $sku = $this->parseSku($item['sku'] ?? '');
            $item['website_ids'] = isset($item['website_ids']) ? explode(',', $item['website_ids']) : [];
            $item[self::IS_NEW_SKU] = false;
            $result[$sku] = $item;
        }

        return $result;
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
