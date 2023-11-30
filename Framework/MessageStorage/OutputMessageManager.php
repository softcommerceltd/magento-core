<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\MessageStorage;

use Magento\Framework\Message\ManagerInterface;
use SoftCommerce\Core\Framework\MessageStorageInterface;
use SoftCommerce\Core\Model\Source\StatusInterface;

/**
 * @inheritDoc
 */
class OutputMessageManager implements OutputMessageManagerInterface
{
    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var OutputHtmlInterface
     */
    private OutputHtmlInterface $outputHtml;

    /**
     * @var StatusPredictionInterface
     */
    private StatusPredictionInterface $statusPrediction;

    /**
     * @param ManagerInterface $messageManager
     * @param OutputHtmlInterface $outputHtml
     * @param StatusPredictionInterface $statusPrediction
     */
    public function __construct(
        ManagerInterface $messageManager,
        OutputHtmlInterface $outputHtml,
        StatusPredictionInterface $statusPrediction
    ) {
        $this->messageManager = $messageManager;
        $this->outputHtml = $outputHtml;
        $this->statusPrediction = $statusPrediction;
    }

    /**
     * @param array $data
     * @param string|null $lineBreak
     */
    public function execute(array $data, ?string $lineBreak = null): void
    {
        foreach ($data as $items) {
            if (null !== $lineBreak) {
                $item = $this->outputHtml->execute(
                    $items,
                    [
                        OutputHtmlInterface::LINE_BREAK => $lineBreak,
                    ]
                );
                $status = $this->statusPrediction->execute($items);
                $this->addMessage($item, $status);
                continue;
            }

            foreach ($items as $item) {
                if (!isset($item[MessageStorageInterface::MESSAGE])) {
                    continue;
                }
                $status = $item[MessageStorageInterface::STATUS] ?? $this->statusPrediction->execute($items);
                $this->addMessage($item[MessageStorageInterface::MESSAGE], $status);
            }
        }
    }

    /**
     * @param $message
     * @param string $status
     */
    private function addMessage($message, string $status): void
    {
        switch ($status) {
            case StatusInterface::CRITICAL:
            case StatusInterface::ERROR:
            case StatusInterface::FAILED:
                $this->messageManager->addErrorMessage($message);
                break;
            case StatusInterface::WARNING:
                $this->messageManager->addWarningMessage($message);
                break;
            case StatusInterface::NOTICE:
                $this->messageManager->addNoticeMessage($message);
                break;
            default:
                $this->messageManager->addSuccessMessage($message);
        }
    }
}
