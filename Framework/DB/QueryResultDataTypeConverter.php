<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\DB;

use Magento\Framework\App\ResourceConnection;

/**
 * @inheritDoc
 */
class QueryResultDataTypeConverter implements QueryResultDataTypeConverterInterface
{
    /**
     * @var array
     */
    protected array $schemaInMemory = [];

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {}

    /**
     * @inheritDoc
     */
    public function execute(string $dbTableName, array $data): array
    {
        $schema = $this->getSchema($dbTableName);
        foreach ($data as &$item) {
            foreach ($item as $index => &$value) {
                $dataType = $schema[$index]['DATA_TYPE'] ?? '';
                $value = $this->castToStrictDataType($dataType, $value);
            }
        }

        return $data;
    }

    /**
     * @param string $dbTableName
     * @return array
     */
    private function getSchema(string $dbTableName): array
    {
        if (!isset($this->schemaInMemory[$dbTableName])) {
            $connection = $this->resourceConnection->getConnection();
            $this->schemaInMemory[$dbTableName] = $connection->describeTable(
                $connection->getTableName($dbTableName)
            );
        }

        return $this->schemaInMemory[$dbTableName] ?? [];
    }

    /**
     * @param string $dataType
     * @param string|mixed $data
     * @return bool|float|int|string
     */
    private function castToStrictDataType(string $dataType, $data)
    {
        switch ($dataType) {
            case 'bool':
                return (bool) $data;
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
            case 'numeric':
                return (int) $data;
            case 'float':
            case 'decimal':
                return (float) $data;
        }

        return (string) $data;
    }
}
