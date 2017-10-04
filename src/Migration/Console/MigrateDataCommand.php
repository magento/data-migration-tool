<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Console;

use Migration\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateDataCommand extends AbstractMigrateCommand
{
    /**
     * @var \Migration\Mapper\Interactive
     */
    protected $mapper;

    /**
     * @var \Migration\Mode\Data
     */
    private $dataMode;

    /**
     * @param \Migration\Config $config
     * @param \Migration\Logger\Manager $logManager
     * @param \Migration\App\Progress $progress
     * @param \Migration\Mode\Data $dataMode
     */
    public function __construct(
        \Migration\Config $config,
        \Migration\Logger\Manager $logManager,
        \Migration\App\Progress $progress,
        \Migration\Mode\Data $dataMode,
        \Migration\Mapper\Interactive $mapper
    ) {
        $this->dataMode = $dataMode;
        $this->mapper = $mapper;
        parent::__construct($config, $logManager, $progress);
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('migrate:data')
            ->setDescription('Main migration of data');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->dataMode->run();
        } catch (Exception $e) {

            if (empty($this->dataMode->notMappedDocuments) && empty($this->dataMode->notMappedDocumentFields)) {
                throw $e;
            }

            $this->mapper->init($input, $output, $this->getHelper('question'), $this->dataMode);
        }
    }
}
