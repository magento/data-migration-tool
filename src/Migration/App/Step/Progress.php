<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

/**
 * Class Progress
 */
class Progress
{
    const RESULT_KEY = 'result';

    const PROCESS_KEY = 'process';

    /**
     * @var string
     */
    protected $lockFileName = 'migration-tool-progress.lock';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    protected $filesystem;

    /**
     * @param \Magento\Framework\Filesystem\Driver\File $filesystem
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    /**
     * Load progress from serialized file
     * @return $this
     */
    protected function loadData()
    {
        if (empty($this->data) && $this->filesystem->isExists($this->getLockFile())) {
            $data = @unserialize($this->filesystem->fileGetContents($this->getLockFile()));
            if (is_array($data)) {
                $this->data = $data;
            }
        }
        return $this;
    }

    /**
     * Writing data to lock file
     *
     * @return bool
     */
    protected function saveData()
    {
        if ($this->filesystem->isExists($this->getLockFile())) {
            $this->filesystem->filePutContents($this->getLockFile(), serialize($this->data));
            return true;
        }
        return false;
    }

    /**
     * @param mixed $object
     * @param string $stage
     * @param bool $result
     * @return $this
     */
    public function saveResult($object, $stage, $result)
    {
        $name = $this->getName($object);
        $this->data[$name][$stage][self::RESULT_KEY] = $result;
        $this->saveData();
        return $this;
    }

    /**
     * @param mixed $object
     * @param string $stage
     * @return bool
     */
    public function isCompleted($object, $stage)
    {
        $this->loadData();
        $name = $this->getName($object);
        return !empty($this->data[$name][$stage][self::RESULT_KEY]);
    }

    /**
     * @param mixed $object
     * @param string $stage
     * @param array $processedEntities
     * @return void
     */
    public function saveProcessedEntities($object, $stage, array $processedEntities)
    {
        $this->loadData();
        $name = $this->getName($object);
        $this->data[$name][$stage][self::PROCESS_KEY] = $processedEntities;
        $this->saveData();
    }

    /**
     * @param mixed $object
     * @param string $stage
     * @param string $entity
     * @return void
     */
    public function addProcessedEntity($object, $stage, $entity)
    {
        $entities = $this->getProcessedEntities($object, $stage);
        if (!in_array($entity, $entities)) {
            $entities[] = $entity;
            $this->saveProcessedEntities($object, $stage, $entities);
        }
    }

    /**
     * @param mixed $object
     * @param string $stage
     * @return void
     */
    public function resetProcessedEntities($object, $stage)
    {
        return $this->saveProcessedEntities($object, $stage, []);
    }

    /**
     * @param mixed $object
     * @param string $stage
     * @return array
     */
    public function getProcessedEntities($object, $stage)
    {
        $this->loadData();
        $name = $this->getName($object);
        if (!empty($this->data[$name][$stage][self::PROCESS_KEY])) {
            return $this->data[$name][$stage][self::PROCESS_KEY];
        }
        return [];
    }

    /**
     * @param mixed $object
     * @return void
     */
    public function reset($object)
    {
        $this->loadData();
        if (!empty($this->data[$this->getName($object)])) {
            unset($this->data[$this->getName($object)]);
            $this->filesystem->filePutContents($this->getLockFile(), serialize($this->data));
        }
    }

    /**
     * @return string
     */
    protected function getLockFile()
    {
        $lockFileDir = dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR .'var';
        $lockFile = $lockFileDir . DIRECTORY_SEPARATOR . $this->lockFileName;
        if (!$this->filesystem->isExists($lockFile)) {
            $this->filesystem->filePutContents($lockFile, 0);
        }
        return $lockFile;
    }

    /**
     * @return $this
     */
    public function clearLockFile()
    {
        $this->filesystem->filePutContents($this->getLockFile(), 0);
        return $this;
    }

    /**
     * @param mixed $object
     * @return null|string
     */
    protected function getName($object)
    {
        return get_class($object);
    }
}
