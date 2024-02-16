<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use SoftCommerce\Core\Model\Eav\AttributeManagementInterface;
use SoftCommerce\Core\Model\Eav\GetAttributeBackendTableInterface;
use SoftCommerce\Core\Model\Utils\GetEntityMetadataInterface;

/**
 * @inheritDoc
 */
class GetProductAttributeData implements GetProductAttributeDataInterface
{
    /**
     * @var AttributeManagementInterface
     */
    private AttributeManagementInterface $attributeManagement;

    /**
     * @var array
     */
    private array $attributeSetIdInMemory = [];

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    /**
     * @var array
     */
    private array $dataInMemory = [];

    /**
     * @var int|null
     */
    private ?int $entityIdInMemory = null;

    /**
     * @var GetAttributeBackendTableInterface
     */
    private GetAttributeBackendTableInterface $getAttributeBackendTable;

    /**
     * @var GetEntityMetadataInterface
     */
    private GetEntityMetadataInterface $getEntityMetadata;

    /**
     * @var int|null
     */
    private ?int $storeIdInMemory = null;

    /**
     * @var array
     */
    private array $tableDescriptionInMemory = [];

    /**
     * @param AttributeManagementInterface $attributeManagement
     * @param GetAttributeBackendTableInterface $getAttributeBackendTable
     * @param GetEntityMetadataInterface $getEntityMetadata
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        AttributeManagementInterface $attributeManagement,
        GetAttributeBackendTableInterface $getAttributeBackendTable,
        GetEntityMetadataInterface $getEntityMetadata,
        ResourceConnection $resourceConnection
    ) {
        $this->attributeManagement = $attributeManagement;
        $this->getAttributeBackendTable = $getAttributeBackendTable;
        $this->getEntityMetadata = $getEntityMetadata;
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * @inheritDoc
     */
    public function execute(
        int $entityId,
        int $storeId = Store::DEFAULT_STORE_ID,
        ?int $attributeSetId = null,
        bool $shouldIncludeStaticAttributes = false
    ): static
    {
        $this->entityIdInMemory = $entityId;
        $this->storeIdInMemory = $storeId;

        if (!$attributeSetId) {
            $attributeSetId = $this->getAttributeSetId();
        }

        if (!isset($this->dataInMemory[$this->entityIdInMemory][$this->storeIdInMemory])) {
            $this->dataInMemory[$this->entityIdInMemory][$this->storeIdInMemory] = $this->retrieveData(
                $attributeSetId,
                $shouldIncludeStaticAttributes
            );
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        return $this->dataInMemory[$this->entityIdInMemory][$this->storeIdInMemory] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getDataByAttributeCode(array|string $attributeCode): array|string|null
    {
        if (is_array($attributeCode)) {
            return array_intersect_key(
                $this->dataInMemory[$this->entityIdInMemory][$this->storeIdInMemory] ?? [],
                array_flip($attributeCode)
            );
        }

        return isset($this->dataInMemory[$this->entityIdInMemory][$this->storeIdInMemory][$attributeCode])
            ? (string) $this->dataInMemory[$this->entityIdInMemory][$this->storeIdInMemory][$attributeCode]
            : null;
    }

    /**
     * @inheritDoc
     */
    public function getDataByAttributeId(array|int $attributeId): array
    {
        $attributeIds = is_array($attributeId) ? $attributeId : [$attributeId];
        $attributeCodes = [];

        foreach (array_map('intval', $attributeIds) as $id) {
            if ($attributeCode = $this->attributeManagement->getAttributeToIdToCodeMapping($id)) {
                $attributeCodes[$id] = $attributeCode;
            }
        }

        return $this->getDataByAttributeCode($attributeCodes);
    }

    /**
     * @inheritDoc
     */
    public function resetData(?int $entityId = null, ?int $storeId = null): void
    {
        if (null === $entityId) {
            $this->dataInMemory = [];
            $this->entityIdInMemory = null;
            $this->storeIdInMemory = null;
            return;
        }

        if (null !== $storeId) {
            unset($this->dataInMemory[$entityId][$storeId]);
        } else {
            unset($this->dataInMemory[$entityId]);
        }
    }

    /**
     * @param int $attributeSetId
     * @param bool $shouldIncludeStaticAttributes
     * @return array
     * @throws LocalizedException
     */
    private function retrieveData(
        int $attributeSetId,
        bool $shouldIncludeStaticAttributes
    ): array {
        $entityLinkField = $this->getEntityMetadata->getLinkField();
        $resultData = [];
        $staticEntityAttributes = [];
        $dynamicEntityAttributes = [];
        $staticTable = null;

        foreach ($this->attributeManagement->getAttributeIdsByAttributeSet($attributeSetId) as $attributeId) {
            $attribute = $this->attributeManagement->getAttributeData($attributeId);

            if (!$attributeCode = $attribute['attribute_code'] ?? null) {
                continue;
            }

            $attributeTable = $attribute['backend_table'] ?? $this->getAttributeBackendTable->execute(
                $attributeId,
                ProductAttributeInterface::ENTITY_TYPE_CODE
            );

            if ($this->attributeManagement->isAttributeStatic($attributeId)) {
                if ($shouldIncludeStaticAttributes
                    && in_array($attributeCode, $this->getTableDescription($attributeTable))
                ) {
                    $staticEntityAttributes[] = $attributeCode;
                    $staticTable = $attributeTable;
                }
            } else {
                $dynamicEntityAttributes[$attributeTable][$attributeId] = $attributeCode;
            }
        }

        if ($staticEntityAttributes && $staticTable) {
            $select = $this->connection->select()->from($staticTable, $staticEntityAttributes);

            if ($staticTable !== 'catalog_product_entity') {
                $select->join(
                    ['cpe' => $this->connection->getTableName('catalog_product_entity')],
                    "cpe.$entityLinkField = $staticTable.$entityLinkField"
                )->where('e.entity_id = ?', $this->entityIdInMemory);
            } else {
                $select->where("$staticTable.$entityLinkField = ?", $this->entityIdInMemory);
            }

            $resultData = $this->connection->fetchRow($select);
        }

        foreach ($dynamicEntityAttributes as $table => $item) {
            $attributeIds = array_keys($item);

            $defaultJoinCondition = [
                $this->connection->quoteInto(
                    'default_value.attribute_id IN (?)',
                    $attributeIds,
                    \Zend_Db::INT_TYPE
                ),
                "default_value.$entityLinkField = e.$entityLinkField",
                'default_value.store_id = 0',
            ];

            $select = $this->connection->select()
                ->from(['e' => $this->connection->getTableName('catalog_product_entity')], [])
                ->joinLeft(
                    ['default_value' => $table],
                    implode(' AND ', $defaultJoinCondition),
                    null
                )
                // ->where("e.entity_id = :entity_id")
                ->where("e.$entityLinkField = :$entityLinkField");

            $bind = [$entityLinkField => $this->entityIdInMemory];

            if ($this->storeIdInMemory !== Store::DEFAULT_STORE_ID) {
                $valueFragmentExpression = $this->connection->getCheckSql(
                    'store_value.value IS NULL',
                    'default_value.value',
                    'store_value.value'
                );

                $attributeIdFragmentExpression = $this->connection->getCheckSql(
                    'store_value.attribute_id IS NULL',
                    'default_value.attribute_id',
                    'store_value.attribute_id'
                );

                $quoteCondition = [
                    $this->connection->quoteInto(
                        'store_value.attribute_id IN (?)',
                        $attributeIds,
                        \Zend_Db::INT_TYPE
                    ),
                    "store_value.$entityLinkField = e.$entityLinkField",
                    'store_value.store_id = :store_id',
                ];

                $select->joinLeft(
                    ['store_value' => $table],
                    implode(' AND ', $quoteCondition),
                    [
                        'attribute_id' => $attributeIdFragmentExpression,
                        'attr_value' => $valueFragmentExpression
                    ]
                );

                $bind['store_id'] = $this->storeIdInMemory;
            } else {
                $select->columns(
                    ['attribute_id' => 'attribute_id', 'attr_value' => 'value'],
                    'default_value'
                );
            }

            foreach ($this->connection->fetchPairs($select, $bind) as $attributeId => $value) {
                if ($attributeCode = $item[$attributeId] ?? null) {
                    $resultData[$attributeCode] = $value;
                }
            }
        }

        return $resultData;
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function getAttributeSetId(): int
    {
        if (!isset($this->attributeSetIdInMemory[$this->entityIdInMemory])) {
            $entityLinkField = $this->getEntityMetadata->getLinkField();
            $select = $this->connection->select()
                ->from($this->connection->getTableName('catalog_product_entity'), 'attribute_set_id')
                ->where("$entityLinkField = ?", $this->entityIdInMemory);

            $this->attributeSetIdInMemory[$this->entityIdInMemory] = (int) $this->connection->fetchOne($select);
        }

        return $this->attributeSetIdInMemory[$this->entityIdInMemory];
    }

    /**
     * @param string $tableName
     * @return array
     */
    private function getTableDescription(string $tableName): array
    {
        if (!isset($this->tableDescriptionInMemory[$tableName])) {
            $this->tableDescriptionInMemory[$tableName] = array_keys($this->connection->describeTable($tableName));
        }

        return $this->tableDescriptionInMemory[$tableName];
    }
}
