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
     * @var AdapterInterface|null
     */
    private ?AdapterInterface $connection = null;

    /**
     * @var string[]|null
     */
    private ?array $dataInMemory = null;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetEntityMetadataInterface $getEntityMetadata
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private GetEntityMetadataInterface $getEntityMetadata,
    ) {}

    /**
     * @inheritDoc
     */
    public function executeAllBySku(string $sku): bool
    {
        if (null === $this->dataInMemory) {
            $select = $this->getConnection()->select()
                ->from(
                    ['cpsl' => $this->getConnection()->getTableName('catalog_product_super_link')],
                    null
                )
                ->joinLeft(
                    ['cpe' => $this->getConnection()->getTableName('catalog_product_entity')],
                    'cpsl.product_id = cpe.entity_id',
                    ['cpe.sku', 'cpe.entity_id']
                );

            $this->dataInMemory = $this->getConnection()->fetchPairs($select);
        }

        return isset($this->dataInMemory[$sku]);
    }

    /**
     * @inheritDoc
     */
    public function executeSingle(int $productId): bool
    {
        $linkTable = $this->getConnection()->getTableName('catalog_product_super_link');
        $entityTable = $this->getConnection()->getTableName('catalog_product_entity');
        $entityLinkField = $this->getEntityMetadata->getLinkField();

        $select = $this->getConnection()->select()
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

        return (bool) $this->getConnection()->fetchOne($select);
    }

    /**
     * @inheritDoc
     */
    public function executeBatch(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $linkTable = $this->getConnection()->getTableName('catalog_product_super_link');
        $entityTable = $this->getConnection()->getTableName('catalog_product_entity');
        $entityLinkField = $this->getEntityMetadata->getLinkField();

        $select = $this->getConnection()->select()
            ->distinct()
            ->from(['sl' => $linkTable], ['child_id' => 'sl.product_id'])
            ->join(
                ['pe' => $entityTable],
                "pe.`$entityLinkField` = sl.`parent_id` AND pe.`type_id` = 'configurable'",
                []
            )
            ->where('sl.product_id IN (?)', $productIds);

        /** @var string[] $childIds */
        $childIds = $this->getConnection()->fetchCol($select);

        $result = array_fill_keys($productIds, false);
        foreach ($childIds as $id) {
            $id = (int) $id;
            $result[$id] = true;
        }

        return $result;
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resourceConnection->getConnection();
        }
        return $this->connection;
    }
}
