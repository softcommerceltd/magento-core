<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Utils;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @inheritDoc
 */
class GetEntityMetadata implements GetEntityMetadataInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var string[]
     */
    private $identifierField;

    /**
     * @var string[]
     */
    private $linkField;

    /**
     * @param MetadataPool $metadataPool
     */
    public function __construct(MetadataPool $metadataPool)
    {
        $this->metadataPool = $metadataPool;
    }

    /**
     * @inheritDoc
     */
    public function getLinkField(string $entityType = ProductInterface::class): string
    {
        if (!isset($this->linkField[$entityType])) {
            $this->linkField[$entityType] = $this->metadataPool
                ->getMetadata($entityType)
                ->getLinkField();
        }
        return $this->linkField[$entityType];
    }

    /**
     * @inheritDoc
     */
    public function getIdentifierField(string $entityType = ProductInterface::class): string
    {
        if (!isset($this->identifierField[$entityType])) {
            $this->identifierField[$entityType] = $this->metadataPool
                ->getMetadata($entityType)
                ->getIdentifierField();
        }
        return $this->identifierField[$entityType];
    }
}
