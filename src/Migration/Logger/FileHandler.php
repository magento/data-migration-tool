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
    protected $permissions = 0777;

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
        $record['formatted'] = $this->getFormatter()->format($record);
        if ($logFile) {
            $filePath = $this->getFilePath($logFile);
            $this->filesystem->filePutContents($filePath, $record['formatted'] . PHP_EOL, FILE_APPEND);
        }
        return false === $this->bubble;
    }

    /**
     * @param string $logFile
     * @return string
     */
    protected function getFilePath($logFile)
    {
        $logFileDir = dirname($logFile);
        if (!$this->filesystem->getRealPath($logFileDir)) {
            if (substr($logFileDir, 0, 1) != '/') {
                $logFileDir = __DIR__ . '/../../../' . $logFileDir;
                $logFile = $logFileDir . '/' . basename($logFile);
            }
            if (!$this->filesystem->isExists($logFileDir)) {
                $this->filesystem->createDirectory($logFileDir, $this->permissions);
            }
        }
        return $logFile;
    }
}
