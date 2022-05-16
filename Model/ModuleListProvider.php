<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\Core\Model;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * @inheritDoc
 */
class ModuleListProvider implements ModuleListProviderInterface
{
    private $data;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * Reader of composer.json files
     *
     * @var Reader
     */
    private $reader;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param ComponentRegistrar $componentRegistrar
     * @param Reader $reader
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ComponentRegistrar $componentRegistrar,
        Reader $reader,
        SerializerInterface $serializer
    ) {
        $this->reader = $reader;
        $this->componentRegistrar = $componentRegistrar;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function getList(?string $moduleName = null, ?string $metadata = null)
    {
        if (null === $this->data) {
            $this->initData();
        }

        if (null === $moduleName) {
            return $this->data;
        }

        return null !== $metadata
            ? ($this->data[$moduleName][$metadata] ?? null)
            : ($this->data[$moduleName] ?? null);
    }

    /**
     * @return void
     */
    private function initData(): void
    {
        $jsonData = $this->reader->getComposerJsonFiles()->toArray();
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
            $index = "$moduleDir/composer.json";
            if (!$this->isVendorPackage($moduleName) || !$packageData = $jsonData[$index] ?? []) {
                continue;
            }

            try {
                $packageData = $this->serializer->unserialize($packageData);
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            $this->data[$moduleName] = [
                'name' =>  $moduleName,
                'package_name' =>  $packageData['name'] ?? '',
                'package_description' =>  $packageData['description'] ?? '',
                'package_version' =>  $packageData['version'] ?? '',
            ];
        }
    }

    /**
     * @param string $packageName
     * @return bool
     */
    private function isVendorPackage(string $packageName): bool
    {
        return strpos(strtolower($packageName), 'softcommerce_') === 0;
    }
}
