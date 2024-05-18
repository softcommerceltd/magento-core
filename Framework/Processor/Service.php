<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\Processor;

use Magento\Framework\Api\SearchCriteriaBuilder;
use SoftCommerce\Core\Framework\DataStorageInterface;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterface;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use function usort;

/**
 * Class Service
 * used to manage process services.
 */
class Service implements ServiceInterface
{
    /**
     * @var ServiceInterface|null
     */
    protected $context;

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var DataStorageInterfaceFactory
     */
    protected DataStorageInterfaceFactory $dataStorageFactory;

    /**
     * @var MessageStorageInterfaceFactory
     */
    protected MessageStorageInterfaceFactory $messageStorageFactory;

    /**
     * @var DataStorageInterface
     */
    protected DataStorageInterface $dataStorage;

    /**
     * @var DataStorageInterface
     */
    protected DataStorageInterface $responseStorage;

    /**
     * @var DataStorageInterface
     */
    protected DataStorageInterface $requestStorage;

    /**
     * @var MessageStorageInterface
     */
    protected MessageStorageInterface $messageStorage;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var array
     */
    protected array $response = [];

    /**
     * @var array
     */
    protected array $request = [];

    /**
     * @var string
     */
    protected string $typeId = '';

    /**
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->dataStorageFactory = $dataStorageFactory;
        $this->messageStorageFactory = $messageStorageFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->data = $data;
        $this->dataStorage = $this->dataStorageFactory->create();
        $this->requestStorage = $this->dataStorageFactory->create();
        $this->responseStorage = $this->dataStorageFactory->create();
        $this->messageStorage = $this->messageStorageFactory->create();
    }

    /**
     * @return $this
     */
    public function initialize(): static
    {
        $this->request =
        $this->response =
            [];
        $this->dataStorage->resetData();
        $this->requestStorage->resetData();
        $this->responseStorage->resetData();
        $this->messageStorage->resetData();
        return $this;
    }

    /**
     * @return $this
     */
    public function finalize(): static
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDataStorage(): DataStorageInterface
    {
        return $this->dataStorage;
    }

    /**
     * @inheritDoc
     */
    public function getMessageStorage(): MessageStorageInterface
    {
        return $this->messageStorage;
    }

    /**
     * @inheritDoc
     */
    public function getRequestStorage(): DataStorageInterface
    {
        return $this->requestStorage;
    }

    /**
     * @inheritDoc
     */
    public function getResponseStorage(): DataStorageInterface
    {
        return $this->responseStorage;
    }

    /**
     * @param int|string|null $key
     * @return mixed
     */
    protected function getData($key = null): mixed
    {
        return null !== $key
            ? ($this->data[$key] ?? null)
            : ($this->data ?: []);
    }

    /**
     * @param mixed $data
     * @param int|string|null $key
     * @return $this
     */
    public function setData(mixed $data, $key = null): static
    {
        if (null !== $key) {
            $this->data[$key] = $data;
        } else {
            $this->data = is_array($data) ? $data : [$data];
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTypeId(): string
    {
        return $this->typeId;
    }

    /**
     * @inheritDoc
     */
    public function init(?ServiceInterface $context = null): static
    {
        $this->context = $context;
        $this->setData($context->getData());
        return $this;
    }

    /**
     * @param ServiceInterface $context
     * @param ProcessorInterface[] $instances
     */
    protected function initTypeInstances($context, array $instances): void
    {
        $this->context = $context;
        foreach ($instances as $instance) {
            $instance->init($context);
        }
    }

    /**
     * @param ProcessorInterface[] $services
     * @return array
     */
    protected function initServices(array $services): array
    {
        if (empty($services)) {
            return [];
        }

        usort($services, function (array $a, array $b) {
            return $this->getSortOrder($a) <=> $this->getSortOrder($b);
        });

        $result = [];
        foreach ($services as $service) {
            if (isset($service['typeId'])) {
                $result[$service['typeId']] = $service['class'] ?? null;
            }
        }

        return $result;
    }

    /**
     * @param array $item
     * @return int
     */
    private function getSortOrder(array $item): int
    {
        return (int) ($item['sortOrder'] ?? 0);
    }
}
