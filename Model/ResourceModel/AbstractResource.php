<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Serialize\SerializerInterface;
use function array_unshift;

/**
 * Class AbstractResource
 * @inheritDoc
 */
abstract class AbstractResource extends AbstractDb
{
    /**
     * @var array
     */
    private $metadata;

    /**
     * @param SerializerInterface $serializer
     * @param Context $context
     * @param null $connectionName
     */
    public function __construct(
        SerializerInterface $serializer,
        Context $context,
        $connectionName = null
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $connectionName);
    }

    /**
     * @param $entityId
     * @param string|array $cols
     * @return array
     * @throws LocalizedException
     */
    public function getByEntityId($entityId, $cols = '*')
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), $cols)
            ->where($this->getIdFieldName() . ' = ?', $entityId);

        return $connection->fetchRow($select);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $condition
     * @param string|array $cols
     * @return array
     * @throws LocalizedException
     */
    public function getByFieldValue(string $field, $value, string $condition = '= ?', $cols = '*'): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), $cols)
            ->where($field . $condition, $value);

        return $connection->fetchAll($select);
    }

    /**
     * @param array $whereCondition
     * @param int|null $limit
     * @param string|array $cols
     * @return array
     * @throws LocalizedException
     */
    public function getEntries(array $whereCondition = [], ?int $limit = null, $cols = '*'): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), $cols);

        foreach ($whereCondition as $condition => $value) {
            $select->where($condition, $value);
        }

        if (null !== $limit) {
            $select->limit($limit);
        }

        return $connection->fetchAll($select);
    }

    /**
     * @param $searchValue
     * @param string $searchField
     * @param string $column
     * @return array
     * @throws LocalizedException
     */
    public function getColumnData(
        $searchValue,
        string $searchField = 'parent_id',
        string $column = 'entity_id'
    ): array {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), $column)
            ->where($searchField . ' IN (?)', is_array($searchValue) ? $searchValue : [$searchValue]);

        return $connection->fetchCol($select) ?: [];
    }

    /**
     * @return string|null
     * @throws LocalizedException
     */
    public function getLastCollectedAt(): ?string
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), ['collected_at']);
        $select->order('collected_at ' . Select::SQL_DESC);
        return $connection->fetchOne($select) ?: null;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getLastInsertId(): int
    {
        return (int) $this->getConnection()->lastInsertId(
            $this->getMainTable()
        );
    }

    /**
     * @param string|null $tableName
     * @param bool $includeDefaultValue
     * @return array
     * @throws LocalizedException
     */
    public function getMeta(?string $tableName = null, bool $includeDefaultValue = false): array
    {
        if (!$this->metadata) {
            foreach ($this->getConnection()->describeTable(
                null !== $tableName
                    ? $this->getTable($tableName)
                    : $this->getMainTable()
            ) as $index => $item) {
                $this->metadata[$index] = $item['DEFAULT'] ?? null;
            }
        }
        return false === $includeDefaultValue
            ? array_keys($this->metadata)
            : $this->metadata;
    }

    /**
     * @param array $item
     * @return array
     * @throws LocalizedException
     */
    public function resolveMetaDataMapping(array $item)
    {
        $result = [];
        foreach ($this->getMeta(null, true) as $metadata => $defaultValue) {
            if (array_key_exists($metadata, $item)) {
                $result[$metadata] = $item[$metadata] ?? $defaultValue;
            }
        }
        return $result;
    }

    /**
     * @param array $item
     * @return array|bool[]|string[]
     * @throws LocalizedException
     */
    public function buildDataForSave(array $item): array
    {
        $item = $this->resolveMetaDataMapping($item);
        return array_map(
            function ($element) {
                if (is_array($element)) {
                    try {
                        $element = $this->serializer->serialize($element);
                    } catch (\InvalidArgumentException $e) {
                        $element = $e->getMessage();
                    }
                }
                return $element;
            },
            $item
        );
    }

    /**
     * @param string $prefix
     * @param string $tableName
     * @return array
     * @throws LocalizedException
     */
    public function getPrefixedMetadata(string $prefix, string $tableName): array
    {
        $arrayKeyRequest = $this->getMeta($tableName);
        $keyArrayResponse = array_map(
            function ($element) use ($prefix) {
                return $prefix . $element;
            },
            $arrayKeyRequest
        );
        return array_combine($keyArrayResponse, array_values($arrayKeyRequest));
    }

    /**
     * @param string $subtractString
     * @param string $tableName
     * @return array
     * @throws LocalizedException
     */
    public function getSubtractedMetadata(string $subtractString, string $tableName): array
    {
        $arrayKeyRequest = $this->getMeta($tableName);
        $keyArrayResponse = array_map(
            function ($element) use ($subtractString) {
                return str_replace($subtractString, '', $element);
            },
            $arrayKeyRequest
        );
        return array_combine($keyArrayResponse, array_values($arrayKeyRequest));
    }

    /**
     * @param string|null $fieldName
     * @return array
     * @throws LocalizedException
     */
    public function getAllIds(?string $fieldName = null): array
    {
        if (null === $fieldName) {
            $fieldName = $this->getIdFieldName();
        }
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), [$fieldName]);
        return $this->getConnection()->fetchCol($select) ?: [];
    }

    /**
     * @param string|int $status
     * @param array $entityIds
     * @return int
     * @throws LocalizedException
     */
    public function updateStatus($status = 'pending', array $entityIds = [])
    {
        $data = ['status' => $status];
        $where = empty($entityIds) ? '' : [$this->getIdFieldName() . ' in (?)' => $entityIds];

        return $this->getConnection()->update(
            $this->getMainTable(),
            $data,
            $where
        );
    }

    /**
     * @param array $bind
     * @param string|array $where
     * @return int
     * @throws LocalizedException
     */
    public function update(array $bind, $where = '')
    {
        return $this->getConnection()->update($this->getMainTable(), $bind, $where);
    }

    /**
     * @param array $data
     * @param array $fields
     * @return int
     * @throws LocalizedException
     */
    public function insertOnDuplicate(array $data, array $fields = [])
    {
        return $this->getConnection()->insertOnDuplicate($this->getMainTable(), $data, $fields);
    }

    /**
     * @param array $dataBind
     * @return int
     * @throws LocalizedException
     */
    public function insert(array $dataBind)
    {
        return $this->getConnection()->insert($this->getMainTable(), $dataBind);
    }

    /**
     * @param array $data
     * @param array $fields
     * @param string $primaryKey
     * @param string|null $pairKey
     * @return array [returns last insert IDs]
     * @throws LocalizedException
     */
    public function saveOnDuplicate(
        array $data,
        array $fields = [],
        string $primaryKey = 'entity_id',
        ?string $pairKey = null
    ) {
        $requestRowCount = count(array_keys($data));
        $responseRowCount = $this->insertOnDuplicate($data, $fields);
        $lastInsertId = $this->getLastInsertId();

        $cols = [$primaryKey];
        if (null !== $pairKey) {
            array_unshift($cols, $pairKey);
        }

        $select = $this->getConnection()->select()->from(['main_tb' => $this->getMainTable()], $cols);
        if (null !== $pairKey && $requestIds = array_column($data, $pairKey)) {
            $select->where('main_tb.' . $pairKey . ' in (?)', $requestIds);
            return $this->getConnection()->fetchPairs($select);
        }

        $condition = $requestRowCount != $responseRowCount
            ? ' <= ?'
            : ' >= ?';

        $select->where("main_tb.$primaryKey $condition", $lastInsertId)
            ->order("$primaryKey " . Select::SQL_DESC)
            ->limit(min($requestRowCount, $responseRowCount));

        if (null !== $pairKey) {
            $result = $this->getConnection()->fetchPairs($select);
        } else {
            $result = $this->getConnection()->fetchCol($select);
        }

        return $result;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function truncateTable()
    {
        if ($this->getConnection()->getTransactionLevel() > 0) {
            $this->getConnection()->delete($this->getMainTable());
        } else {
            $this->getConnection()->truncateTable($this->getMainTable());
        }
        return $this;
    }

    /**
     * @param $where
     * @return int
     * @throws LocalizedException
     */
    public function remove($where)
    {
        return $this->getConnection()->delete($this->getMainTable(), $where);
    }

    /**
     * @param string|null $defaultValue
     * @return false|string
     */
    private function getMetaDefaultValueMap(?string $defaultValue)
    {
        return $defaultValue === 'current_timestamp()'
            ? date('Y-m-d H:i:s')
            : $defaultValue;
    }
}
