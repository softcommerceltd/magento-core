<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\Processor;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\Core\Model\Source\StatusInterface;

/**
 * @inheritDoc
 */
class Processor extends Service implements ProcessorInterface
{
    /**
     * @var ProcessorInterface[]
     */
    protected array $processors;

    /**
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     * @param array $processors
     */
    public function __construct(
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = [],
        array $processors = []
    ) {
        $this->processors = $this->initServices($processors);
        parent::__construct($dataStorageFactory, $messageStorageFactory, $searchCriteriaBuilder, $data);
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        $this->initialize();

        foreach ($this->processors as $processor) {
            try {
                $processor->execute();
            } catch (\Exception $e) {
                $this->getContext()->getMessageStorage()->addData(
                    $e->getMessage(),
                    $processor->getTypeId() ?: get_class($processor),
                    StatusInterface::ERROR
                );
            }
        }

        $this->finalize();
    }

    /**
     * @return Processor
     */
    public function initialize(): static
    {
        $this->initTypeInstances($this->context, $this->processors);
        return parent::initialize();
    }

    /**
     * @inheritDoc
     */
    public function getContext()
    {
        if (null === $this->context) {
            throw new LocalizedException(__('Context object is not set.'));
        }

        return $this->context;
    }

    /**
     * @param $context
     * @return $this
     */
    public function setContext($context): static
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function init(?ServiceInterface $context = null): static
    {
        foreach ($this->processors as $processor) {
            $processor->init($context);
        }

        return parent::init($context);
    }
}
