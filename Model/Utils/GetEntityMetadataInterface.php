<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Utils;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Interface GetEntityMetadataInterface
 * used to get entity metadata info.
 */
interface GetEntityMetadataInterface
{
    /**
     * Get link field name for entity relationships
     *
     * Returns the field name used for JOINs and relationships in entity-related tables.
     * This field is used for referencing entities in associated tables (bundle options,
     * configurable links, EAV attributes, etc.).
     *
     * Platform differences:
     * - Magento Open Source: Returns 'entity_id'
     * - Adobe Commerce (with staging): Returns 'row_id'
     *
     * Usage: Use this for foreign key relationships in staging-aware tables.
     *
     * @param string $entityType Entity class name (default: ProductInterface::class)
     * @return string Field name ('row_id' or 'entity_id')
     */
    public function getLinkField(string $entityType = ProductInterface::class): string;

    /**
     * Get identifier field name for entity
     *
     * Returns the primary key field name for the entity table.
     * This field uniquely identifies an entity across all versions and is immutable.
     *
     * Platform differences:
     * - Magento Open Source: Returns 'entity_id' (same as link field)
     * - Adobe Commerce (with staging): Returns 'entity_id' (permanent entity ID)
     *
     * Usage: Use this for permanent entity identification, URLs, and external references.
     *
     * @param string $entityType Entity class name (default: ProductInterface::class)
     * @return string Field name (always 'entity_id')
     * @throws \Exception
     */
    public function getIdentifierField(string $entityType = ProductInterface::class): string;

    /**
     * @param string $entityType
     * @return int
     * @throws \Exception
     */
    public function generateIdentifier(string $entityType = ProductInterface::class): int;

    /**
     * Check if Adobe Commerce with Catalog Staging is enabled
     *
     * @return bool
     */
    public function isStagingEnabled(): bool;
}
