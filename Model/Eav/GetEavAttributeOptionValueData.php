<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Eav;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @inheritDoc
 */
class GetEavAttributeOptionValueData implements GetEavAttributeOptionValueDataInterface
{
    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    /**
     * @var array
     */
    private array $data = [];

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
    }

    /**
     * @inheritDoc
     */
    public function execute(int $attributeId): array
    {
        if (!isset($this->data[$attributeId])) {
            $this->data[$attributeId] = $this->getData($attributeId);
        }

        return $this->data[$attributeId] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function resetData(?int $attributeId = null): void
    {
        if (null !== $attributeId && isset($this->data[$attributeId])) {
            $this->data[$attributeId] = null;
        }
        $this->data = [];
    }

    /**
     * @param int $attributeId
     * @return array
     */
    private function getData(int $attributeId): array
    {
        $select = $this->connection->select()
            ->from($this->connection->getTableName('eav_attribute_option'))
            ->where('attribute_id = ?', $attributeId);

        $result = [];
        foreach ($this->connection->fetchAll($select) as $item) {
            if (!$optionId = (int) ($item['option_id'] ?? null)) {
                continue;
            }

            $selectValue = $this->connection->select()
                ->from(
                    $this->connection->getTableName('eav_attribute_option_value'),
                    [
                        'store_id',
                        'value'
                    ]
                )
                ->where('option_id = ?', $optionId);

            $values = [];
            foreach ($this->connection->fetchAll($selectValue) as $value) {
                if (!isset($value['store_id'], $value['value'])) {
                    continue;
                }

                if ($value['store_id'] == 0) {
                    $item['value'] = $value['value'];
                    continue;
                }

                $values[$value['store_id']] = $value['value'];
            }

            $item['values'] = $values;
            $result[$optionId] = $item;
        }

        return $result;
    }
}
