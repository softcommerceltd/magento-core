<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Utils;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use function explode;
use function strtolower;
use function trim;

/**
 * @inheritDoc
 */
class SkuStorage implements SkuStorageInterface
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var GetEntityMetadataInterface
     */
    private $getEntityMetadata;

    /**
     * @var array
     */
    private $skuData;

    /**
     * @param GetEntityMetadataInterface $getEntityMetadata
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        GetEntityMetadataInterface $getEntityMetadata,
        ResourceConnection $resourceConnection
    ) {
        $this->getEntityMetadata = $getEntityMetadata;
        $this->connection = $resourceConnection->getConnection();
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
    public function isExistsSku(string $sku): bool
    {
        return (bool) $this->getData($sku);
    }

    /**
     * @inheritDoc
     */
    public function isNewSku(string $sku): bool
    {
        return (bool) $this->getData($sku, self::IS_NEW_SKU);
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
        $columns = ['entity_id', 'type_id', 'attribute_set_id', 'sku', 'plenty_item_id', 'plenty_variation_id'];
        if ($this->getEntityMetadata->getLinkField() !== $this->getEntityMetadata->getIdentifierField()) {
            $columns[] = $this->getEntityMetadata->getLinkField();
        }

        $select = $this->connection->select()
            ->from(
                ['main_tb' => $this->connection->getTableName('catalog_product_entity')],
                $columns
            )
            ->joinLeft(
                ['cpw_tb' => $this->connection->getTableName('catalog_product_website')],
                'main_tb.entity_id = cpw_tb.product_id',
                [
                    'website_ids' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT cpw_tb.website_id)')
                ]
            )->group(
                'main_tb.entity_id'
            );

        $result = [];
        foreach ($this->connection->fetchAll($select) as $item) {
            $sku = $this->parseSku($item['sku'] ?? '');
            $item['website_ids'] = isset($item['website_ids']) ? explode(',', $item['website_ids']) : [];
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
