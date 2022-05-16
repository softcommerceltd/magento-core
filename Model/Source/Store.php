<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * @inheritDoc
 */
class Store implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * Store constructor.
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return $this->storeRepository->getList();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $stores = $this->storeRepository->getList();
            foreach ($stores as $store) {
                $this->options[] = [
                    'value' => $store->getId(),
                    'label' => $store->getName()
                ];
            }
        }

        return $this->options;
    }
}
