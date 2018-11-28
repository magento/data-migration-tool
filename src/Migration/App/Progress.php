<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

/**
 * Class Progress
 */
class Progress
{
    const RESULT_KEY = 'result';

    const PROCESS_KEY = 'process';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var Progress\File
     */
    protected $file;

    /**
     * @param Progress\File $file
     */
    public function __construct(
        Progress\File $file
    ) {
        $this->file = $file;
    }

    /**
     * Save result
     *
     * @param mixed $object
     * @param string $stage
     * @param bool $result
     * @return $this
     */
    public function saveResult($object, $stage, $result)
    {
        $data = $this->file->getData();
        $name = $this->getName($object);
        $data[$name][$stage][self::RESULT_KEY] = $result;
        $this->file->saveData($data);
        return $this;
    }

    /**
     * Is completed
     *
     * @param mixed $object
     * @param string $stage
     * @return bool
     */
    public function isCompleted($object, $stage)
    {
        $data = $this->file->getData();
        $name = $this->getName($object);
        return !empty($data[$name][$stage][self::RESULT_KEY]);
    }

    /**
     * Save processed entities
     *
     * @param mixed $object
     * @param string $stage
     * @param array $processedEntities
     * @return void
     */
    public function saveProcessedEntities($object, $stage, array $processedEntities)
    {
        $data = $this->file->getData();
        $name = $this->getName($object);
        $data[$name][$stage][self::PROCESS_KEY] = $processedEntities;
        $this->file->saveData($data);
    }

    /**
     * Add processed entity
     *
     * @param mixed $object
     * @param string $stage
     * @param string $entity
     * @return bool
     */
    public function addProcessedEntity($object, $stage, $entity)
    {
        $entities = $this->getProcessedEntities($object, $stage);
        if (in_array($entity, $entities)) {
            return false;
        }
        $entities[] = $entity;
        $this->saveProcessedEntities($object, $stage, $entities);
        return true;
    }

    /**
     * Reset processed entities
     *
     * @param mixed $object
     * @param string $stage
     * @return void
     */
    public function resetProcessedEntities($object, $stage)
    {
        $this->saveProcessedEntities($object, $stage, []);
    }

    /**
     * Get processed entities
     *
     * @param mixed $object
     * @param string $stage
     * @return array
     */
    public function getProcessedEntities($object, $stage)
    {
        $data = $this->file->getData();
        $name = $this->getName($object);
        if (!empty($data[$name][$stage][self::PROCESS_KEY])) {
            return $data[$name][$stage][self::PROCESS_KEY];
        }
        return [];
    }

    /**
     * Reset
     *
     * @param mixed|null $object
     * @return void
     */
    public function reset($object = null)
    {
        if (empty($object)) {
            $this->file->clearLockFile();
            return;
        } else {
            $data = $this->file->getData();
            if (!empty($data[$this->getName($object)])) {
                unset($data[$this->getName($object)]);
                $this->file->saveData($data);
            }
        }
    }

    /**
     * Get name
     *
     * @param mixed $object
     * @return null|string
     */
    protected function getName($object)
    {
        if (is_string($object)) {
            $name = $object;
        } else {
            $name = get_class($object);
        }
        
        return $name;
    }
}
