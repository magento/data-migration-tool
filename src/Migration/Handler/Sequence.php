<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;
use Migration\ResourceModel\Destination;

/**
 * Handler to create new record in Sequence table
 */
class Sequence extends AbstractHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var array
     */
    protected $sequenceTablesCleaned = [];

    /**
     * @var string
     */
    private $createdVersionField = 'created_in';

    /**
     * @var string
     */
    private $updatedVersionField = 'updated_in';

    /**
     * @var int
     */
    private $minVersion = 1;

    /**
     * @var int
     */
    private $maxVersion = 2147483647;

    /**
     * @param string $table
     * @param Destination $destination
     */
    public function __construct($table, Destination $destination)
    {
        $this->table = $table;
        $this->destination = $destination;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        if (!in_array($this->table, $this->sequenceTablesCleaned)) {
            $this->destination->clearDocument($this->table);
            $this->sequenceTablesCleaned[] = $this->table;
        }
        $this->validate($recordToHandle);
        $id = $recordToHandle->getValue($this->field);
        if ($id && $this->table) {
            $this->destination->saveRecords($this->table, [['sequence_value' => $id]]);
        }
        if (!array_diff([$this->createdVersionField, $this->updatedVersionField], $oppositeRecord->getFields())) {
            $oppositeRecord->setValue($this->createdVersionField, $this->minVersion);
            $oppositeRecord->setValue($this->updatedVersionField, $this->maxVersion);
        }
    }
}
