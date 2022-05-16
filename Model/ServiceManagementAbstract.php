<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model;

use SoftCommerce\Core\Framework\DataStorageInterface;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterface;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;

/**
 * Class ServiceManagementAbstract used as a
 * wrapper class for service management.
 */
class ServiceManagementAbstract
{
    /**
     * @var DataStorageInterfaceFactory
     */
    protected $dataStorageFactory;

    /**
     * @var MessageStorageInterfaceFactory
     */
    protected $messageStorageFactory;

    /**
     * @var DataStorageInterface
     */
    private $dataStorage;

    /**
     * @var DataStorageInterface
     */
    private $responseStorage;

    /**
     * @var DataStorageInterface
     */
    protected $requestStorage;

    /**
     * @var MessageStorageInterface
     */
    protected $messageStorage;

    /**
     * @var array
     */
    protected $response;

    /**
     * @var array
     */
    protected $request;

    /**
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     */
    public function __construct(
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory
    ) {
        $this->dataStorageFactory = $dataStorageFactory;
        $this->messageStorageFactory = $messageStorageFactory;
        $this->dataStorage = $this->dataStorageFactory->create();
        $this->requestStorage = $this->dataStorageFactory->create();
        $this->responseStorage = $this->dataStorageFactory->create();
        $this->messageStorage = $this->messageStorageFactory->create();
    }

    /**
     * @return $this
     */
    public function initialize()
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
    public function finalize()
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
     * @return MessageStorageInterface
     */
    public function getMessageStorage(): MessageStorageInterface
    {
        return $this->messageStorage;
    }
}
