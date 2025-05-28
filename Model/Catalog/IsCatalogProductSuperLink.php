<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use SoftCommerce\Core\Model\Utils\GetEntityMetadataInterface;

/**
 * @inheritDoc
 */
class IsCatalogProductSuperLink implements IsCatalogProductSuperLinkInterface
{
    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    /**
     * @var GetEntityMetadataInterface
     */
    private GetEntityMetadataInterface $getEntityMetadata;

    /**
     * @var string[]|null
     */
    private ?array $dataInMemory = null;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetEntityMetadataInterface $getEntityMetadata
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetEntityMetadataInterface $getEntityMetadata,
    ) {
        $this->connection = $resourceConnection->getConnection();
        $this->getEntityMetadata = $getEntityMetadata;
    }

    /**
     * @inheritDoc
     */
    public function executeAllBySku(string $sku): bool
    {
        if (null === $this->dataInMemory) {
            $select = $this->connection->select()
                ->from(
                    ['cpsl' => $this->connection->getTableName('catalog_product_super_link')],
                    null
                )
                ->joinLeft(
                    ['cpe' => $this->connection->getTableName('catalog_product_entity')],
                    'cpsl.product_id = cpe.entity_id',
                    ['cpe.sku', 'cpe.entity_id']
                );

            $this->dataInMemory = $this->connection->fetchPairs($select);
        }

        return isset($this->dataInMemory[$sku]);
    }

    /**
     * @inheritDoc
     */
    public function executeSingle(int $productId): bool
    {
        $linkTable = $this->connection->getTableName('catalog_product_super_link');
        $entityTable = $this->connection->getTableName('catalog_product_entity');
        $entityLinkField = $this->getEntityMetadata->getLinkField();

        $select = $this->connection->select()
            ->from([], ['is_child' => new \Zend_Db_Expr(
                "EXISTS(
                    SELECT 1
                        FROM `{$linkTable}` AS sl
                    JOIN `{$entityTable}` AS pe
                        ON pe.`$entityLinkField` = sl.`parent_id`
                        AND pe.`type_id`     = 'configurable'
                    WHERE sl.`product_id` = {$productId}
                )"
            )]);

        return (bool) $this->connection->fetchOne($select);
    }

    /**
     * @inheritDoc
     */
    public function executeBatch(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $linkTable = $this->connection->getTableName('catalog_product_super_link');
        $entityTable = $this->connection->getTableName('catalog_product_entity');
        $entityLinkField = $this->getEntityMetadata->getLinkField();

        $select = $this->connection->select()
            ->distinct()
            ->from(['sl' => $linkTable], ['child_id' => 'sl.product_id'])
            ->join(
                ['pe' => $entityTable],
                "pe.`$entityLinkField` = sl.`parent_id` AND pe.`type_id` = 'configurable'",
                []
            )
            ->where('sl.product_id IN (?)', $productIds);

        /** @var string[] $childIds */
        $childIds = $this->connection->fetchCol($select);

        $result = array_fill_keys($productIds, false);
        foreach ($childIds as $id) {
            $id = (int) $id;
            $result[$id] = true;
        }

        return $result;
    }
}
