<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

use Magento\CatalogImportExport\Model\Import\Uploader;
use Magento\CatalogImportExport\Model\Import\UploaderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Io\File;
use function md5_file;

/**
 * @inheritDoc
 */
class FileImageManagement implements FileImageManagementInterface
{
    /**
     * @var string|null
     */
    private ?string $catalogProductDirectory = null;

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var Uploader|null
     */
    private ?Uploader $fileUploader = null;

    /**
     * @var string|null
     */
    private ?string $importDirectory = null;

    /**
     * @var File
     */
    private File $ioFile;

    /**
     * @var WriteInterface
     */
    private WriteInterface $mediaDirectory;

    /**
     * @var UploaderFactory
     */
    private UploaderFactory $uploaderFactory;

    /**
     * @param DirectoryList $directoryList
     * @param File $ioFile
     * @param Filesystem $filesystem
     * @param UploaderFactory $uploaderFactory
     * @throws FileSystemException
     */
    public function __construct(
        DirectoryList $directoryList,
        File $ioFile,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory
    ) {
        $this->directoryList = $directoryList;
        $this->ioFile = $ioFile;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->uploaderFactory = $uploaderFactory;
    }

    /**
     * @inheritDoc
     */
    public function getImportDirectory(): string
    {
        if (null === $this->importDirectory) {
            $this->importDirectory = $this->directoryList->getPath(DirectoryList::MEDIA) . '/import';
        }
        return $this->importDirectory;
    }

    /**
     * @inheritDoc
     */
    public function getCatalogProductDirectory(): string
    {
        if (null === $this->catalogProductDirectory) {
            $this->catalogProductDirectory = $this->directoryList->getPath(DirectoryList::MEDIA) . '/catalog/product';
        }
        return $this->catalogProductDirectory;
    }

    /**
     * @inheritDoc
     */
    public function getImageFile(string $fileName, ?string $directory = null): ?string
    {
        if (null === $directory) {
            $directory = $this->getImportDirectory();
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;
        return $this->isFileSourceable($filePath) ? $filePath : null;
    }

    /**
     * @inheritDoc
     */
    public function getImageMd5Checksum(string $filePath): ?string
    {
        if (empty($filePath) || !$this->isFileSourceable($filePath)) {
            return null;
        }

        return md5_file($filePath);
    }

    /**
     * @inheritDoc
     */
    public function getFileInfo(string $filePath, ?string $index = null)
    {
        $pathInfo = $this->ioFile->getPathInfo($filePath);
        return null !== $index
            ? ($pathInfo[$index] ?? '')
            : $pathInfo;
    }

    /**
     * @inheritDoc
     */
    public function getFileUploader(): Uploader
    {
        if (null === $this->fileUploader) {
            $this->fileUploader = $this->uploaderFactory->create();
            $this->fileUploader->init();
            $dirConfig = DirectoryList::getDefaultConfig();
            $dirAddon = $dirConfig[DirectoryList::MEDIA][DirectoryList::PATH];
            $tmpPath = $dirAddon . '/' . $this->mediaDirectory->getRelativePath('import');

            if (!$this->fileUploader->setTmpDir($tmpPath)) {
                throw new LocalizedException(
                    __('File directory \'%1\' is not readable.', $tmpPath)
                );
            }

            $destinationDir = "catalog/product";
            $destinationPath = $dirAddon . '/' . $this->mediaDirectory->getRelativePath($destinationDir);

            $this->mediaDirectory->create($destinationPath);
            if (!$this->fileUploader->setDestDir($destinationPath)) {
                throw new LocalizedException(
                    __('File directory \'%1\' is not writable.', $destinationPath)
                );
            }
        }
        return $this->fileUploader;
    }

    /**
     * @inheritDoc
     */
    public function downloadImageFile(string $url, string $imageName, ?string $md5Checksum = null): string
    {
        $directory = $this->getImportDirectory();
        $this->ioFile->checkAndCreateFolder($directory);
        $filePath = $directory . DIRECTORY_SEPARATOR . $imageName;

        if (!$this->ioFile->fileExists($filePath)) {
            $this->ioFile->read($url, $filePath);
            return $filePath;
        }

        if (null !== $md5Checksum && $md5Checksum !== md5_file($filePath)) {
            $this->ioFile->rm($filePath);
            $this->ioFile->read($url, $filePath);
        }

        return $filePath;
    }

    /**
     * @inheritDoc
     */
    public function uploadImageFile(string $filePath, bool $renameFileOff = false): string
    {
        $res = $this->getFileUploader()->move($filePath, $renameFileOff);
        return $res['file'] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function isFileSourceable(string $filePath): bool
    {
        return $this->mediaDirectory->isExist($filePath) && $this->mediaDirectory->isReadable($filePath);
    }
}
