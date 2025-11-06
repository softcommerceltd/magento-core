<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Framework\MessageStorage;

use Magento\Framework\Phrase;
use SoftCommerce\Core\Framework\IsMultidimensionalArrayInterface;
use SoftCommerce\Core\Framework\MessageStorageInterface;
use SoftCommerce\Core\Model\Source\StatusInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @inheritDoc
 * @deprecated in favour of
 * @see \SoftCommerce\Core\Framework\MessageCollectorInterface
 */
class OutputConsole implements OutputConsoleInterface
{
    /**
     * @param IsMultidimensionalArrayInterface $isMultidimensionalArray
     * @param StatusPredictionInterface $statusPrediction
     */
    public function __construct(
        private readonly IsMultidimensionalArrayInterface $isMultidimensionalArray,
        private readonly StatusPredictionInterface $statusPrediction
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(OutputInterface $output, array $data): void
    {
        foreach ($data as $items) {
            if (!is_array($items) || !$this->isMultidimensionalArray->execute($items)) {
                $items = [$items];
            }

            foreach ($items as $item) {
                if (!isset($item[MessageStorageInterface::ENTITY], $item[MessageStorageInterface::MESSAGE])) {
                    continue;
                }

                $output->writeln(
                    sprintf('Processing: <comment>%s</comment>', $item[MessageStorageInterface::ENTITY])
                );

                $output->writeln(
                    $this->getMessage(
                        $item[MessageStorageInterface::MESSAGE],
                        $item[MessageStorageInterface::STATUS] ?? $this->statusPrediction->execute($items)
                    )
                );
            }
        }
    }

    /**
     * @param $message
     * @param string $status
     * @return string
     */
    private function getMessage($message, string $status): string
    {
        if ($message instanceof Phrase) {
            $message = $message->render();
        }

        switch ($status) {
            case StatusInterface::CRITICAL:
            case StatusInterface::ERROR:
            case StatusInterface::FAILED:
                $html = "<error> -- $message</error>";
                break;
            case StatusInterface::NOTICE:
            case StatusInterface::WARNING:
                $html = "<comment> -- $message</comment>";
                break;
            default:
                $html = "<info> -- $message</info>";
        }

        return $html;
    }
}
