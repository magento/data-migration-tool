<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateDataCommand
 */
class MigrateDataCommand extends AbstractMigrateCommand
{
    /**
     * @var \Migration\Mode\Data
     */
    private $dataMode;

    /**
     * @var string
     */
    private $name = 'migrate:data';

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
        \Migration\Mode\Data $dataMode
    ) {
        $this->dataMode = $dataMode;
        parent::__construct($config, $logManager, $progress);
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName($this->name)
            ->setDescription('Main migration of data');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dataMode->run();
    }
}
