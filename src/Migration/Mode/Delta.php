<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Mode;

use Migration\App\Progress;
use Migration\App\Step\StageInterface;
use Migration\Logger\Logger;
use Migration\Exception;
use Migration\App\Mode\StepList;
use Migration\Reader\Groups;
use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Source;

/**
 * Class Delta
 */
class Delta extends AbstractMode implements \Migration\App\Mode\ModeInterface
{
    /**
     * @var int
     */
    protected $autoRestart;

    /**
     * @inheritdoc
     */
    protected $mode = 'delta';

    /**
     * @var bool
     */
    protected $canBeCompleted = false;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Groups
     */
    protected $groupsReader;

    /**
     * @var \Migration\Reader\GroupsFactory
     */
    private $groupsFactory;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param \Migration\App\Mode\StepListFactory $stepListFactory
     * @param Source $source
     * @param \Migration\Reader\GroupsFactory $groupsFactory
     * @param int $autoRestart
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        \Migration\App\Mode\StepListFactory $stepListFactory,
        Source $source,
        \Migration\Reader\GroupsFactory $groupsFactory,
        $autoRestart = 5
    ) {
        $this->source = $source;
        $this->autoRestart = $autoRestart;
        $this->groupsFactory = $groupsFactory;
        parent::__construct($progress, $logger, $stepListFactory);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        do {
            /** @var StepList $steps */
            $steps = $this->stepListFactory->create(['mode' => 'delta']);
            /**
             * @var string $stepName
             * @var StageInterface[] $step
             */
            foreach ($steps->getSteps() as $stepName => $step) {
                if (empty($step['delta'])) {
                    continue;
                }
                $this->runDelta($step, $stepName);
                if (!empty($step['volume'])) {
                    $this->runVolume($step, $stepName);
                }
            }

            $deltaLogs = $this->getGroupsReader()->getGroups();
            foreach ($deltaLogs as $deltaDocuments) {
                foreach (array_keys($deltaDocuments) as $documentName) {
                    /** @var Mysql $adapter */
                    $adapter = $this->source->getAdapter();
                    $adapter->deleteProcessedRecords(
                        $this->source->addDocumentPrefix(
                            $this->source->getDeltaLogName($documentName)
                        )
                    );
                }
            }

            $this->logger->info('Migration completed successfully');
            if ($this->autoRestart) {
                $this->logger->info("Automatic restart in {$this->autoRestart} sec. Use CTRL-C to abort");
                sleep($this->autoRestart);
            }
        } while ($this->autoRestart);
        return true;
    }

    /**
     * Run delta
     *
     * @param array $step
     * @param string $stepName
     * @throws Exception
     * @return void
     */
    private function runDelta(array $step, $stepName)
    {
        if (!$this->runStage($step['delta'], $stepName, 'delta delivering')) {
            throw new Exception('Delta delivering failed');
        }
    }

    /**
     * Run volume
     *
     * @param array $step
     * @param string $stepName
     * @throws Exception
     * @return void
     */
    private function runVolume(array $step, $stepName)
    {
        if (!$this->runStage($step['volume'], $stepName, 'volume check', true)) {
            $this->logger->warning('Volume Check failed');
        }
    }

    /**
     * Get groups reader
     *
     * @return Groups
     */
    private function getGroupsReader()
    {
        if (null == $this->groupsReader) {
            $this->groupsReader = $this->groupsFactory->create('delta_document_groups_file');
        }
        return $this->groupsReader;
    }
}
