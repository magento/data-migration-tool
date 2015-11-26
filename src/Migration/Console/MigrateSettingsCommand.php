<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateSettingsCommand extends AbstractMigrateCommand
{
    /**
     * @var \Migration\Mode\Settings
     */
    private $settingsMode;

    /**
     * @param \Migration\Config $config
     * @param \Migration\Logger\Manager $logManager
     * @param \Migration\App\Progress $progress
     * @param \Migration\Mode\Settings\Proxy $settingsMode
     */
    public function __construct(
        \Migration\Config $config,
        \Migration\Logger\Manager $logManager,
        \Migration\App\Progress $progress,
        \Migration\Mode\Settings\Proxy $settingsMode
    ) {
        $this->settingsMode = $settingsMode;
        parent::__construct($config, $logManager, $progress);
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('migrate:settings')
            ->setDescription('Migrate system configuration');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->settingsMode->run();
    }
}
