<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * @inheritDoc
 */
class AbstractModel extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @param SerializerInterface $serializer
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        protected readonly SerializerInterface $serializer,
        Context $context,
        Registry $registry,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param string $metadata
     * @return array
     */
    public function getDataSerialized(string $metadata): array
    {
        if (!$data = $this->getData($metadata)) {
            return [];
        }

        try {
            $data = is_array($data)
                ? $data
                : $this->serializer->unserialize($data);
        } catch (\Exception $e) {
            return [];
        }

        return is_array($data) ? $data : [$data];
    }

    /**
     * @param string $metadata
     * @param array $data
     * @return $this
     */
    public function setDataSerialized(string $metadata, array $data)
    {
        try {
            $data = $this->serializer->serialize($data);
        } catch (\Exception $e) {
            return $this;
        }

        $this->setData($metadata, $data);
        return $this;
    }
}
