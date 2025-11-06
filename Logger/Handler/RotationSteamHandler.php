<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\LogRecord;

/**
 * @inheritDoc
 */
class RotationSteamHandler extends RotatingFileHandler
{
    /**
     * @var string
     */
    protected string $fileName = '/var/log/softcommerce/core.log';

    /**
     * @param DriverInterface $filesystem
     * @param string $fileName
     * @param int $maxFiles
     */
    public function __construct(
        protected readonly DriverInterface $filesystem,
        string $fileName,
        int $maxFiles = 5
    ) {
        if (!empty($fileName)) {
            $this->fileName = $fileName;
        }

        $stream = BP . DIRECTORY_SEPARATOR . $this->fileName;
        parent::__construct($stream, $maxFiles);
        $this->dateFormat = self::FILE_PER_MONTH;
        $this->setFormatter(new LineFormatter(null, null, true, true));
    }

    /**
     * @inheritDoc
     */
    protected function write(array|LogRecord $record): void
    {
        $logDirectory = $this->filesystem->getParentDirectory($this->url);
        if (!$this->filesystem->isDirectory($logDirectory)) {
            $this->filesystem->createDirectory($logDirectory);
        }

        parent::write($record);
    }
}
