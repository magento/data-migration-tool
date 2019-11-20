<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateSettingsCommand
 */
class MigrateSettingsCommand extends AbstractMigrateCommand
{
    /**
     * @var \Migration\Mode\Settings
     */
    private $settingsMode;

    /**
     * @var string
     */
    private $name = 'migrate:settings';

    /**
     * @param \Migration\Config $config
     * @param \Migration\Logger\Manager $logManager
     * @param \Migration\App\Progress $progress
     * @param \Migration\Mode\Settings $settingsMode
     */
    public function __construct(
        \Migration\Config $config,
        \Migration\Logger\Manager $logManager,
        \Migration\App\Progress $progress,
        \Migration\Mode\Settings $settingsMode
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
        $this->setName($this->name)
            ->setDescription('Migrate system configuration');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->settingsMode->run();
    }
}
