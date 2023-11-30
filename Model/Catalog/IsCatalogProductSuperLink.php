<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

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
     * @var string[]|null
     */
    private ?array $dataInMemory = null;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * @inheritDoc
     */
    public function execute(string $sku): bool
    {
        if (null === $this->dataInMemory) {
            $this->dataInMemory = $this->getData();
        }

        return isset($this->dataInMemory[$sku]);
    }

    /**
     * @return array
     */
    private function getData(): array
    {
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

        return $this->connection->fetchPairs($select);
    }
}
