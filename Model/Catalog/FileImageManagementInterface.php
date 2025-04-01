<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model\Catalog;

use Magento\CatalogImportExport\Model\Import\Uploader;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface FileImageManagementInterface
 * used to manage media image files.
 */
interface FileImageManagementInterface
{
    /**
     * @return string
     */
    public function getImportDirectory(): string;

    /**
     * @return string
     */
    public function getCatalogProductDirectory(): string;

    /**
     * @param string $fileName
     * @param string|null $directory
     * @return string|null
     */
    public function getImageFile(string $fileName, ?string $directory = null): ?string;

    /**
     * @param string $filePath
     * @return string|null
     */
    public function getImageMd5Checksum(string $filePath): ?string;

    /**
     * @param string $filePath
     * @param string|null $index
     * @return array|string
     */
    public function getFileInfo(string $filePath, ?string $index = null);

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getFileUploader(): Uploader;

    /**
     * @param string $url
     * @param string $imageName
     * @param string|null $md5Checksum
     * @return string
     * @throws LocalizedException
     */
    public function downloadImageFile(string $url, string $imageName, ?string $md5Checksum = null): string;

    /**
     * @param string $filePath
     * @param bool $renameFileOff
     * @return string
     * @throws LocalizedException
     */
    public function uploadImageFile(string $filePath, bool $renameFileOff = false): string;

    /**
     * @param string $filename
     * @return bool
     */
    public function removeImportFile(string $filename): bool;

    /**
     * @param string $filePath
     * @return bool
     */
    public function isFileSourceable(string $filePath): bool;
}
