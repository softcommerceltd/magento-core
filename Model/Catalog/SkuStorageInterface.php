<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

/**
 * Interface SkuStorageInterface
 * used to provide catalog product entity data.
 */
interface SkuStorageInterface
{
    public const IS_NEW_SKU = 'is_new_sku';
    public const IS_PROCESSED_SKU = 'is_processed_sku';

    /**
     * @param string|null $sku
     * @param string|null $index
     * @param bool $reset
     * @return array|mixed|null
     * @throws \Exception
     */
    public function getData(?string $sku = null, ?string $index = null, bool $reset = false);

    /**
     * @param string $sku
     * @param array|string|mixed $data
     * @param string|null $index
     * @return $this
     */
    public function setData(string $sku, $data, ?string $index = null);

    /**
     * @param int $entityId
     * @param string|null $index
     * @return array|mixed|string|null
     * @throws \Exception
     */
    public function getDataByEntityId(int $entityId, ?string $index = null);

    /**
     * @param string $sku
     * @return bool
     * @throws \Exception
     */
    public function isSkuExists(string $sku): bool;

    /**
     * @param string $sku
     * @return bool
     * @throws \Exception
     */
    public function isNewSku(string $sku): bool;

    /**
     * @param string $sku
     * @return bool
     * @throws \Exception
     */
    public function isProcessedSku(string $sku): bool;
}
