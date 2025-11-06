<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model;

use SoftCommerce\Core\Framework\DataStorageInterface;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageCollectorInterface;
use SoftCommerce\Core\Framework\MessageCollectorInterfaceFactory;

/**
 * Class ServiceManagementAbstract used as a
 * wrapper class for service management.
 */
class ServiceManagementAbstract
{
    /**
     * New message collector for structured message handling
     * Replaces MessageStorage with format-agnostic collection
     *
     * @var MessageCollectorInterface
     */
    protected MessageCollectorInterface $messageCollector;

    /**
     * @var DataStorageInterface
     */
    private DataStorageInterface $dataStorage;

    /**
     * @var DataStorageInterface
     */
    private DataStorageInterface $responseStorage;

    /**
     * @var DataStorageInterface
     */
    protected DataStorageInterface $requestStorage;

    /**
     * @var array
     */
    protected array $response;

    /**
     * @var array
     */
    protected array $request;

    /**
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageCollectorInterfaceFactory $messageCollectorFactory
     */
    public function __construct(
        protected readonly DataStorageInterfaceFactory $dataStorageFactory,
        protected readonly MessageCollectorInterfaceFactory $messageCollectorFactory,
    ) {
        $this->messageCollector = $this->messageCollectorFactory->create();
        $this->dataStorage = $this->dataStorageFactory->create();
        $this->requestStorage = $this->dataStorageFactory->create();
        $this->responseStorage = $this->dataStorageFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function initialize(): static
    {
        $this->request =
        $this->response =
            [];
        $this->dataStorage->resetData();
        $this->requestStorage->resetData();
        $this->messageCollector->reset();
        $this->responseStorage->resetData();
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
     * @return DataStorageInterface
     */
    public function getDataStorage(): DataStorageInterface
    {
        return $this->dataStorage;
    }

    /**
     * @return DataStorageInterface
     */
    public function getRequestStorage(): DataStorageInterface
    {
        return $this->requestStorage;
    }

    /**
     * @return DataStorageInterface
     */
    public function getResponseStorage(): DataStorageInterface
    {
        return $this->responseStorage;
    }

    /**
     * Get message collector for structured message handling
     * Use this for new code instead of getMessageStorage()
     *
     * @return MessageCollectorInterface
     */
    public function getMessageCollector(): MessageCollectorInterface
    {
        return $this->messageCollector;
    }
}
