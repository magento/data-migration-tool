<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateDeltaCommand
 */
class MigrateDeltaCommand extends AbstractMigrateCommand
{
    /**
     * @var \Migration\Mode\Delta
     */
    private $deltaMode;

    /**
     * @var string
     */
    private $name = 'migrate:delta';

    /**
     * @param \Migration\Config $config
     * @param \Migration\Logger\Manager $logManager
     * @param \Migration\App\Progress $progress
     * @param \Migration\Mode\Delta $deltaMode
     */
    public function __construct(
        \Migration\Config $config,
        \Migration\Logger\Manager $logManager,
        \Migration\App\Progress $progress,
        \Migration\Mode\Delta $deltaMode
    ) {
        $this->deltaMode = $deltaMode;
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
            ->setDescription('Migrate the data is added into Magento after the main migration');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->deltaMode->run();
    }

    /**
     * @return bool
     */
    protected function canReset()
    {
        return false;
    }
}
