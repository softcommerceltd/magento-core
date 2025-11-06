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
     * @var string[]
     */
    private array $identifierField = [];

    /**
     * @var string[]
     */
    private array $linkField = [];

    /**
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        private readonly MetadataPool $metadataPool
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getLinkField(string $entityType = ProductInterface::class): string
    {
        if (!isset($this->linkField[$entityType])) {
            try {
                $this->linkField[$entityType] = $this->metadataPool
                    ->getMetadata($entityType)
                    ->getLinkField();
            } catch (\Exception $e) {
                $this->linkField[$entityType] = 'entity_id';
            }
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

    /**
     * @inheritDoc
     */
    public function generateIdentifier(string $entityType = ProductInterface::class): int
    {
        return (int) $this->metadataPool->getMetadata($entityType)->generateIdentifier();
    }

    /**
     * @inheritDoc
     */
    public function isStagingEnabled(): bool
    {
        return $this->getLinkField() !== 'entity_id';
    }
}
