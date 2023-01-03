<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Utils;

/**
 * Interface WebsiteStorageInterface
 * used to provide website & store data.
 */
interface WebsiteStorageInterface
{
    /**
     * @param string|null $websiteCode
     * @return array|int|null
     */
    public function getWebsiteCodeToId(?string $websiteCode = null);

    /**
     * @param string|null $websiteCode
     * @return array
     */
    public function getWebsiteCodeToStoreIds(?string $websiteCode = null): array;

    /**
     * @param int|null $websiteId
     * @return array|string|null
     */
    public function getWebsiteIdToCode(?int $websiteId = null);

    /**
     * @param int|null $websiteId
     * @return array
     */
    public function getWebsiteIdToStoreIds(?int $websiteId = null): array;

    /**
     * @param null|string $storeCode
     * @return array|int|null
     */
    public function getStoreCodeToId(?string $storeCode = null);

    /**
     * @param string|null $storeId
     * @return int[]|int|null
     */
    public function getStoreIdToWebsiteId(?string $storeId = null);

    /**
     * @param int|null $storeId
     * @return array|int|null
     */
    public function getStoreIdToWebsiteStoreIds(?int $storeId = null): array;

    /**
     * @param int $storeId
     * @return array
     */
    public function getStoreById(int $storeId): array;

    /**
     * @param int $websiteId
     * @return array
     */
    public function getWebsiteById(int $websiteId): array;

    /**
     * @return array
     */
    public function getDefaultWebsite(): array;

    /**
     * @return array
     */
    public function getDefaultStore(): array;

    /**
     * @param int $websiteId
     * @return array
     */
    public function getDefaultWebsiteStore(int $websiteId): array;

    /**
     * @return array
     */
    public function getAdminStore(): array;

    /**
     * @param int|null $storeId
     * @return array|int|mixed
     */
    public function getAdminWebsiteStoreId(?int $storeId = null);

    /**
     * @return array
     */
    public function getData(): array;
}
