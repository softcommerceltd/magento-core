<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SoftCommerce\Core\Model\System\Config\Backend;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Registry;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Serialized
 * @package SoftCommerce\Core\Model\Profile\Config\Backend
 */
class Serialized extends Value
{
    /**
     * @var Json|mixed
     */
    protected $serializer;

    /**
     * @var Random
     */
    protected Random $mathRandom;

    /**
     * @param Random $mathRandom
     * @param SerializerInterface $serializer
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Random $mathRandom,
        SerializerInterface $serializer,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->mathRandom = $mathRandom;
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Process data after load
     *
     * @return $this
     */
    public function afterLoad()
    {
        if (!$this->getValue() || is_array($this->getValue())) {
            return $this;
        }

        $value = $this->serializer->unserialize($this->getValue());
        if (!is_array($value)) {
            return $this;
        }

        unset($value['__empty']);
        $this->setValue($value);
        return $this;
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            if (isset($value['__empty'])) {
                unset($value['__empty']);
            }

            if (!empty($value)) {
                $this->setValue($this->serializer->serialize($value));
            }
        }
        parent::beforeSave();
        return $this;
    }
}
