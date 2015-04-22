<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Logger;

use Magento\Framework\Filesystem\Driver\File;
use Migration\Config;

/**
 * Processing logger handler creation for migration application
 */
class FileHandler extends \Monolog\Handler\AbstractHandler implements \Monolog\Handler\HandlerInterface
{
    /**
     * @var File
     */
    protected $filesystem;

    /**
     * Permissions for new sub-directories
     *
     * @var int
     */
    protected $permissions = 0755;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param File $filesystem
     * @param Config $config
     */
    public function __construct(File $filesystem, Config $config)
    {
        $this->filesystem = $filesystem;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return false;
        }
        $logFile = $this->config->getOption('log_file');
        if ($logFile) {
            $filePath = $this->getFilePath($logFile);
            $this->filesystem->filePutContents($filePath, $record['message'], FILE_APPEND);
        }
        return false === $this->bubble;
    }

    /**
     * @param string $logFile
     * @return bool
     */
    protected function getFilePath($logFile)
    {
        if (!$realPath = $this->filesystem->getRealPath($logFile)) {
            $createDir = [];
            while (!$realPath) {
                $pathArray = explode(DIRECTORY_SEPARATOR, $logFile);
                $createDir[] = array_pop($pathArray);
                $logFile = implode(DIRECTORY_SEPARATOR, $pathArray);
                $realPath = $this->filesystem->getRealPath($logFile);
            }
            $logFileName = array_shift($createDir);
            if (!empty($createDir)) {
                $realPath = $realPath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array_reverse($createDir));
                $this->filesystem->createDirectory($realPath, $this->permissions);
            }
            $realPath .= DIRECTORY_SEPARATOR . $logFileName;
        }
        return $realPath;
    }
}
