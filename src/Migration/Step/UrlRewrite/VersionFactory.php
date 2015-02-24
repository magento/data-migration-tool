<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite;

use \Magento\Framework\ObjectManagerInterface;

/**
 * Class VersionFactory
 */
class VersionFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $sourceVersion
     * @param string $destinationVersion
     * @return \Migration\Step\StepInterface
     * @throws \Exception
     */
    public function create($sourceVersion, $destinationVersion)
    {
        $sourceVersion = str_replace('.', '', $sourceVersion);
        $destinationVersion = str_replace('.', '', $destinationVersion);
        $className = "Migration\\Step\\UrlRewrite\\Version{$sourceVersion}to{$destinationVersion}";
        $version = $this->objectManager->create($className);
        if (!($version instanceof \Migration\Step\StepInterface)) {
            throw new \Exception("Class: $className must implement StepInterface.");
        }

        return $version;
    }
}
