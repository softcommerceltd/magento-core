<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\MessageCollector;

use SoftCommerce\Core\Framework\MessageCollectorInterface;
use SoftCommerce\Core\Framework\MessageStorageInterface;
use SoftCommerce\Core\Model\Source\StatusInterface;

/**
 * Adapter to use MessageCollector as MessageStorage
 * 
 * This allows gradual migration from MessageStorage to MessageCollector
 * by implementing the MessageStorage interface using a MessageCollector backend
 */
class MessageStorageAdapter implements MessageStorageInterface
{
    /**
     * @param MessageCollectorInterface $messageCollector
     */
    public function __construct(
        private MessageCollectorInterface $messageCollector
    ) {}

    /**
     * @inheritDoc
     */
    public function getData($entity = null, array $status = []): array
    {
        $messages = null !== $entity 
            ? $this->messageCollector->getEntityMessages((string)$entity)
            : $this->messageCollector->getMessages();

        if (empty($status)) {
            return $messages;
        }

        // Filter by status
        $result = [];
        foreach ($messages as $entityId => $entityMessages) {
            $filtered = array_filter($entityMessages, function($msg) use ($status) {
                return isset($msg['status']) && in_array($msg['status'], $status);
            });
            
            if (!empty($filtered)) {
                $result[$entityId] = $filtered;
            }
        }
        
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getDataByStatus(string $status): array
    {
        return $this->getData(null, [$status]);
    }

    /**
     * @inheritDoc
     */
    public function addData($message, $entity, string $status = StatusInterface::SUCCESS, array $metadata = []): static
    {
        // Convert Phrase to string if needed
        if ($message instanceof \Magento\Framework\Phrase) {
            $message = $message->render();
        }
        
        $this->messageCollector->addMessage(
            (string)$entity,
            (string)$message,
            $status,
            $metadata
        );
        
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setData(array $data): static
    {
        // Reset collector and add all data
        $this->messageCollector->reset();
        
        foreach ($data as $entity => $messages) {
            if (!is_array($messages)) {
                continue;
            }
            
            foreach ($messages as $msg) {
                if (is_array($msg)) {
                    $this->addData(
                        $msg[self::MESSAGE] ?? '',
                        $entity,
                        $msg[self::STATUS] ?? StatusInterface::SUCCESS,
                        $msg[self::METADATA] ?? []
                    );
                } else {
                    $this->addData($msg, $entity);
                }
            }
        }
        
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mergeData(array $data, $key = null): static
    {
        if (null !== $key) {
            // Merge only specific key
            if (isset($data[$key]) && is_array($data[$key])) {
                foreach ($data[$key] as $msg) {
                    if (is_array($msg)) {
                        $this->addData(
                            $msg[self::MESSAGE] ?? '',
                            $key,
                            $msg[self::STATUS] ?? StatusInterface::SUCCESS,
                            $msg[self::METADATA] ?? []
                        );
                    }
                }
            }
        } else {
            // Merge all data
            foreach ($data as $entity => $messages) {
                if (!is_array($messages)) {
                    continue;
                }
                
                foreach ($messages as $msg) {
                    if (is_array($msg)) {
                        $this->addData(
                            $msg[self::MESSAGE] ?? '',
                            $entity,
                            $msg[self::STATUS] ?? StatusInterface::SUCCESS,
                            $msg[self::METADATA] ?? []
                        );
                    }
                }
            }
        }
        
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEntityIds(): array
    {
        return array_keys($this->messageCollector->getMessages());
    }

    /**
     * @inheritDoc
     */
    public function resetData($key = null): static
    {
        if (null === $key) {
            $this->messageCollector->reset();
        } else {
            // MessageCollector doesn't support partial reset, so we need to rebuild
            $messages = $this->messageCollector->getMessages();
            unset($messages[$key]);
            
            $this->messageCollector->reset();
            $this->setData($messages);
        }
        
        return $this;
    }

    /**
     * Get the underlying message collector
     *
     * @return MessageCollectorInterface
     */
    public function getMessageCollector(): MessageCollectorInterface
    {
        return $this->messageCollector;
    }
}