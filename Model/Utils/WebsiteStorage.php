<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Utils;

use Magento\Framework\App\ResourceConnection;
use function array_filter;
use function current;
use function explode;

/**
 * @inheritDoc
 * @deprecated in favour
 * @see \SoftCommerce\Core\Model\Store\WebsiteStorageInterface
 */
class WebsiteStorage implements WebsiteStorageInterface
{
    /**
     * @var array|null
     */
    private ?array $adminStoreInMemory = null;

    /**
     * @var array|null
     */
    private ?array $dataInMemory = null;

    /**
     * @var array|null
     */
    private ?array $websiteCodeToId = null;

    /**
     * @var array|null
     */
    private ?array $websiteCodeToStoreIds = null;

    /**
     * @var array|null
     */
    private ?array $websiteIdToCode = null;

    /**
     * @var array|null
     */
    private ?array $websiteIdToStoreIds = null;

    /**
     * @var array|null
     */
    private ?array $storeCodeToId = null;

    /**
     * @var array|null
     */
    private ?array $storeIdToWebsiteId = null;

    /**
     * @var array|null
     */
    private ?array $storeIdToAdminWebsiteStoreId = null;

    /**
     * @var array|null
     */
    private ?array $storeIdToWebsiteStoreIds = null;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteCodeToId(?string $websiteCode = null)
    {
        if (null === $this->websiteCodeToId) {
            $this->websiteCodeToId = [];
            foreach ($this->getData() as $item) {
                if (isset($item['website_id'], $item['website_code'])) {
                    $this->websiteCodeToId[$item['website_code']] = (int) $item['website_id'];
                }
            }
        }

        return null !== $websiteCode
            ? ($this->websiteCodeToId[$websiteCode] ?? null)
            : $this->websiteCodeToId;
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteCodeToStoreIds(?string $websiteCode = null): array
    {
        if (null === $this->websiteCodeToStoreIds) {
            $this->websiteCodeToStoreIds = [];
            foreach ($this->getData() as $item) {
                if (isset($item['website_code'], $item['store_id'], $item['store_code'])) {
                    $this->websiteCodeToStoreIds[$item['website_code']][$item['store_code']] = (int) $item['store_id'];
                }
            }
        }
        return null !== $websiteCode
            ? ($this->websiteCodeToStoreIds[$websiteCode] ?? [])
            : $this->websiteCodeToStoreIds;
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteIdToCode(?int $websiteId = null)
    {
        if (null === $this->websiteIdToCode) {
            $this->websiteIdToCode = [];
            foreach ($this->getData() as $item) {
                if (isset($item['website_id'], $item['website_code'])) {
                    $this->websiteIdToCode[(int) $item['website_id']] = $item['website_code'];
                }
            }
        }
        return null !== $websiteId
            ? ($this->websiteIdToCode[$websiteId] ?? null)
            : $this->websiteIdToCode;
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteIdToStoreIds(?int $websiteId = null): array
    {
        if (null === $this->websiteIdToStoreIds) {
            $this->websiteIdToStoreIds = [];
            foreach ($this->getData() as $item) {
                if (isset($item['website_id'], $item['store_id'])) {
                    $this->websiteIdToStoreIds[$item['website_id']][$item['store_id']] = (int) $item['store_id'];
                }
            }
        }
        return null !== $websiteId
            ? ($this->websiteIdToStoreIds[$websiteId] ?? [])
            : $this->websiteIdToStoreIds;
    }

    /**
     * @inheritDoc
     */
    public function getStoreCodeToId(?string $storeCode = null)
    {
        if (null === $this->storeCodeToId) {
            $this->storeCodeToId = [];
            foreach ($this->getData() as $item) {
                if (isset($item['store_id'], $item['store_code'])) {
                    $this->storeCodeToId[$item['store_code']] = (int) $item['store_id'];
                }
            }
        }
        return null !== $storeCode
            ? ($this->storeCodeToId[$storeCode] ?? null)
            : $this->storeCodeToId;
    }

    /**
     * @inheritDoc
     */
    public function getStoreIdToWebsiteId(?string $storeId = null)
    {
        if (null === $this->storeIdToWebsiteId) {
            $this->storeIdToWebsiteId = [];
            foreach ($this->getData() as $item) {
                if (isset($item['store_id'], $item['website_id'])) {
                    $this->storeIdToWebsiteId[$item['store_id']] = (int) $item['website_id'];
                }
            }
        }
        return null !== $storeId
            ? ($this->storeIdToWebsiteId[$storeId] ?? null)
            : $this->storeIdToWebsiteId;
    }


    /**
     * @inheritDoc
     */
    public function getStoreIdToWebsiteStoreIds(?int $storeId = null): array
    {
        if (null === $this->storeIdToWebsiteStoreIds) {
            $this->storeIdToWebsiteStoreIds = [];
            foreach ($this->getData() as $item) {
                if (!isset($item['store_id'], $item['store_ids'])) {
                    continue;
                }

                $store = (int) $item['store_id'];
                $storeIds = explode(',', $item['store_ids'] ?? '');
                $storeToStoreIds = [];
                foreach ($storeIds as $id) {
                    $id = (int) $id;
                    $storeToStoreIds[$id] = $id;
                }

                if ($storeToStoreIds) {
                    $this->storeIdToWebsiteStoreIds[$store] = $storeToStoreIds;
                }
            }
        }

        return null !== $storeId
            ? ($this->storeIdToWebsiteStoreIds[$storeId] ?? [])
            : $this->storeIdToWebsiteStoreIds;
    }

    /**
     * @inheritDoc
     */
    public function getStoreById(int $storeId): array
    {
        return current(
            array_filter($this->getData(), function ($item) use ($storeId) {
                return isset($item['store_id']) && $item['store_id'] == $storeId;
            })
        ) ?: [];
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteById(int $websiteId): array
    {
        return current(
            array_filter($this->getData(), function ($item) use ($websiteId) {
                return isset($item['website_id']) && $item['website_id'] == $websiteId;
            })
        ) ?: [];
    }

    /**
     * @inheritDoc
     */
    public function getDefaultWebsite(): array
    {
        return current(
            array_filter($this->getData(), function ($item) {
                return isset($item['is_default_website']) && $item['is_default_website'];
            })
        ) ?: [];
    }

    /**
     * @inheritDoc
     */
    public function getDefaultStoreId(): int
    {
        return (int) ($this->getDefaultWebsite()['default_store_id'] ?? 0);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultStore(): array
    {
        return $this->getStoreById(
            $this->getDefaultStoreId()
        );
    }

    /**
     * @inheritDoc
     */
    public function getDefaultWebsiteStore(int $websiteId): array
    {
        $defaultStoreId = $this->getWebsiteById($websiteId)['default_store_id'] ?? 0;
        return $this->getStoreById((int) $defaultStoreId);
    }

    /**
     * @inheritDoc
     */
    public function getAllStoreIds(?int $excludeId = null): array
    {
        return array_values($this->getStoreCodeToId());
    }

    /**
     * @inheritDoc
     */
    public function getAdminStore(): array
    {
        if (null === $this->adminStoreInMemory) {
            $this->adminStoreInMemory = $this->getStoreById(0);
            $defaultStore = $this->getDefaultStore();
            if (isset($defaultStore['is_default_website'])) {
                $this->adminStoreInMemory['is_default_website'] = $defaultStore['is_default_website'];
            }
            if (isset($defaultStore['store_ids'])) {
                $this->adminStoreInMemory['store_ids'] = $defaultStore['store_ids'];
            }
            if (isset($defaultStore['group_id'])) {
                $this->adminStoreInMemory['group_id'] = $defaultStore['group_id'];
            }
            if (isset($defaultStore['root_category_id'])) {
                $this->adminStoreInMemory['root_category_id'] = $defaultStore['root_category_id'];
            }
            if (isset($defaultStore['default_store_id'])) {
                $this->adminStoreInMemory['default_store_id'] = $defaultStore['default_store_id'];
            }
            if (isset($defaultStore['website_id'])) {
                $this->adminStoreInMemory['default_website_id'] = $defaultStore['website_id'];
            }
        }

        return $this->adminStoreInMemory;
    }

    /**
     * @inheritDoc
     */
    public function getAdminWebsiteStoreId(?int $storeId = null)
    {
        if (null === $this->storeIdToAdminWebsiteStoreId) {
            $this->storeIdToAdminWebsiteStoreId = [];
            foreach ($this->getData() as $item) {
                if (!isset($item['store_id'])) {
                    continue;
                }

                $id = (int) $item['store_id'];
                if (isset($item['is_default_website'], $item['default_store_id']) && $item['is_default_website']) {
                    $this->storeIdToAdminWebsiteStoreId[$id] = 0;
                } else {
                    $this->storeIdToAdminWebsiteStoreId[$id] = $id;
                }
            }
        }

        return null !== $storeId
            ? ($this->storeIdToAdminWebsiteStoreId[$storeId] ?? $storeId)
            : $this->storeIdToAdminWebsiteStoreId;
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        if (null !== $this->dataInMemory) {
            return $this->dataInMemory;
        }

        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                ['sw_tb' => $connection->getTableName('store_website')],
                [
                    'website_id',
                    'website_code' => 'sw_tb.code',
                    'website_name' => 'name',
                    'website_default_group_id' => 'default_group_id',
                    'is_default_website' => 'is_default'
                ]
            )
            ->joinLeft(
                ['s_tb' => $connection->getTableName('store')],
                'sw_tb.website_id = s_tb.website_id',
                [
                    'store_id',
                    'store_code' => 's_tb.code',
                    'store_name' => 'name'
                ]
            )
            ->joinLeft(
                ['sid_tb' => $connection->getTableName('store')],
                'sw_tb.website_id = sid_tb.website_id',
                [
                    'store_ids' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT sid_tb.store_id)')
                ]
            )
            ->joinLeft(
                ['sg_tb' => $connection->getTableName('store_group')],
                'sw_tb.website_id = sg_tb.website_id',
                [
                    'group_id',
                    'group_name' => 'name',
                    'root_category_id',
                    'default_store_id',
                    'group_code' => 's_tb.code'
                ]
            )
            ->group(
                's_tb.store_id'
            );

        $this->dataInMemory = $connection->fetchAll($select);

        return $this->dataInMemory;
    }
}
