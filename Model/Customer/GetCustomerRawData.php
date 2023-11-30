<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * @inheritDoc
 */
class GetCustomerRawData implements GetCustomerRawDataInterface
{
    /**
     * @var array|string[]
     */
    private array $data = [];

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $connection;

    /**
     * @param ResourceConnection $connection
     */
    public function __construct(ResourceConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $email, ?int $websiteId = null, $cols = '*'): array
    {
        if (!isset($this->data[$email])) {
            $this->data[$email] = $this->getData($email, $cols);
        }

        return null !== $websiteId
            ? ($this->data[$email][$websiteId] ?? [])
            : $this->data[$email];
    }

    /**
     * @param string $email
     * @param $cols
     * @return array
     */
    private function getData(string $email, $cols = '*'): array
    {
        if (is_array($cols)) {
            $cols[] = CustomerInterface::WEBSITE_ID;
        }

        $connection = $this->connection->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('customer_entity'), $cols)
            ->where(CustomerInterface::EMAIL . ' = ?', $email);

        $resultToWebsite = [];
        foreach ($connection->fetchAll($select) ?: [] as $item) {
            if (isset($item[CustomerInterface::WEBSITE_ID])) {
                $resultToWebsite[$item[CustomerInterface::WEBSITE_ID]] = $item;
            }
        }

        return $resultToWebsite;
    }
}
