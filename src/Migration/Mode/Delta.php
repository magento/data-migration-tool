<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        $this->groupsReader = $groupsFactory->create('delta_document_groups_file');
        parent::__construct($progress, $logger, $stepListFactory);
    }

    /**
     * {@inheritdoc}
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

            $deltaLogs = $this->groupsReader->getGroups();
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
     * @param array $step
     * @param string $stepName
     * @throws Exception
     * @return void
     */
    private function runVolume(array $step, $stepName)
    {
        if (!$this->runStage($step['volume'], $stepName, 'volume check')) {
            $this->logger->warning('Volume Check failed');
        }
    }
}
