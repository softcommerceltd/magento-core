<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Utils;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;

/**
 * @inheritDoc
 * @deprecated in favour
 * @see \SoftCommerce\Core\Model\Eav\GetEntityTypeIdInterface
 */
class GetEntityTypeId implements GetEntityTypeIdInterface
{
    /**
     * @var array
     */
    private array $entityTypeId = [];

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {}

    /**
     * @inheritDoc
     */
    public function execute(string $entityTypeCode = Product::ENTITY): int
    {
        if (!isset($this->entityTypeId[$entityTypeCode])) {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from($connection->getTableName('eav_entity_type'), ['entity_type_id'])
                ->where('entity_type_code = ?', $entityTypeCode);
            $this->entityTypeId[$entityTypeCode] = (int) $connection->fetchOne($select);
        }
        return $this->entityTypeId[$entityTypeCode];
    }
}
