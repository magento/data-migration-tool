<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Logger;

use Magento\Framework\Filesystem\Driver\File;
use Migration\Config;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Processing logger handler creation for migration application
 */
class FileHandler extends \Monolog\Handler\AbstractHandler implements \Monolog\Handler\HandlerInterface
{
    /**
     * @var File
     */
    protected $file;

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
     * @param File $file
     * @param Config $config
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(File $file, Config $config, \Magento\Framework\Filesystem $filesystem)
    {
        $this->file = $file;
        $this->config = $config;
        $this->filesystem = $filesystem;
        parent::__construct();
    }

    /**
     * @inheritdoc
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
            $this->file->filePutContents($filePath, $record['formatted'] . PHP_EOL, FILE_APPEND);
        }
        return false === $this->bubble;
    }

    /**
     * Get file path
     *
     * @param string $logFile
     * @return string
     */
    protected function getFilePath($logFile)
    {
        $logFileDir = dirname($logFile);
        if (!$this->file->getRealPath($logFileDir)) {
            if (substr($logFileDir, 0, 1) != '/') {
                $logFileDir = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath()
                    . $logFileDir;
                $logFile = $logFileDir . DIRECTORY_SEPARATOR . basename($logFile);
            }
            if (!$this->file->isExists($logFileDir)) {
                $this->file->createDirectory($logFileDir, $this->permissions);
            }
        } elseif ($logFileDir == '.') {
            $logFile = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath()
                . basename($logFile);
        }
        return $logFile;
    }
}
