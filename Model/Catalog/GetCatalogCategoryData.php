<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use SoftCommerce\Core\Model\Utils\GetEntityMetadataInterface;

/**
 * @inheritDoc
 */
class GetCatalogCategoryData implements GetCatalogCategoryDataInterface
{
    /**
     * @var array
     */
    private array $data = [];

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    /**
     * @var GetEntityMetadataInterface
     */
    private GetEntityMetadataInterface $getEntityMetadata;

    /**
     * @param GetEntityMetadataInterface $getEntityMetadata
     * @param ResourceConnection $resource
     */
    public function __construct(
        GetEntityMetadataInterface $getEntityMetadata,
        ResourceConnection $resource
    ) {
        $this->getEntityMetadata = $getEntityMetadata;
        $this->connection = $resource->getConnection();
    }

    /**
     * @inheritDoc
     */
    public function execute(int $entityId): array
    {
        if (!isset($this->data[$entityId])) {
            $identityField = $this->getEntityMetadata->getIdentifierField(CategoryInterface::class);
            $select = $this->getSelect();
            $select->where("cce.$identityField = ?", $entityId);
            $this->data[$entityId] = $this->connection->fetchAssoc($select)[$entityId] ?? [];
        }

        return $this->data[$entityId];
    }

    /**
     * @return Select
     * @throws \Exception
     */
    protected function getSelect(): Select
    {
        $linkField = $this->getEntityMetadata->getLinkField(CategoryInterface::class);
        return $this->connection->select()
            ->from(
                ['cce' => $this->connection->getTableName('catalog_category_entity')],
            )->joinLeft(
                ['eet' => $this->connection->getTableName('eav_entity_type')],
                'eet.entity_type_code = \'catalog_category\'',
                null
            )->joinLeft(
                ['ean' => $this->connection->getTableName('eav_attribute')],
                'ean.attribute_code = \'name\' AND eet.entity_type_id = ean.entity_type_id',
                null
            )->joinLeft(
                ['ccevn' => $this->connection->getTableName('catalog_category_entity_varchar')],
                "cce.$linkField = ccevn.$linkField" .
                ' AND ean.attribute_id = ccevn.attribute_id AND ccevn.store_id = 0',
                ['name' => 'value']
            )->joinLeft(
                ['eap' => $this->connection->getTableName('eav_attribute')],
                'eap.attribute_code = \'plenty_category_id\' AND eet.entity_type_id = eap.entity_type_id',
                null
            )->joinLeft(
                ['ccevp' => $this->connection->getTableName('catalog_category_entity_varchar')],
                "cce.$linkField = ccevp.$linkField" .
                ' AND eap.attribute_id = ccevp.attribute_id AND ccevp.store_id = 0',
                ['client_entity_id' => 'value']
            );
    }
}
