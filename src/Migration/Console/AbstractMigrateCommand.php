<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;

class AbstractMigrateCommand extends Command
{
    /**#@+
     * Keys and shortcuts for input arguments and options
     */
    const INPUT_KEY_CONFIG = 'config';
    const INPUT_KEY_RESET = 'reset';
    /**#@-*/

    /**
     * @var \Migration\Config
     */
    protected $config;

    /**
     * @var \Migration\Logger\Manager
     */
    protected $logManager;

    /**
     * @var \Migration\App\Progress
     */
    protected $progress;

    /**
     * Verbosity levels
     *
     * @var array
     */
    protected $verbosityLevels = [
        2 => \Migration\Logger\Manager::LOG_LEVEL_INFO,
        3 => \Migration\Logger\Manager::LOG_LEVEL_ERROR,
        4 => \Migration\Logger\Manager::LOG_LEVEL_DEBUG
    ];

    /**
     * @param \Migration\Config $config
     * @param \Migration\Logger\Manager $logManager
     * @param \Migration\App\Progress $progress
     */
    public function __construct(
        \Migration\Config $config,
        \Migration\Logger\Manager $logManager,
        \Migration\App\Progress $progress
    ) {
        $this->config = $config;
        $this->logManager = $logManager;
        $this->progress = $progress;

        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setDefinition([
            new InputArgument(
                self::INPUT_KEY_CONFIG,
                InputArgument::OPTIONAL,
                'Path to main configuration file, i.e.: etc/[ce/ee]-to[ce/ee]/[m1-version]/config.xml'
            ),
            new InputOption(
                self::INPUT_KEY_RESET,
                'r',
                InputOption::VALUE_NONE,
                'Reset the current position of migration to start from the beginning'
            ),
        ]);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {

        $config = $input->getArgument(self::INPUT_KEY_CONFIG);

        if (!$config) {
            $dialog = $this->getHelperSet()->get('dialog');
            $config = $dialog->ask($output, '<question>What is the path to your config.xml file?</question> <comment>[./etc/config.xml]</comment> ');
        }

        if (!$config) {
            $output->writeln('<info>The default path ./etc/config.xml will be tried. If this does not exist, copy the config.xml from the etc/[ce/ee]-to-[ce/ee]/[m1-version]/config.xml.dist to a new file.</info>');
        }

        $this->config->init($config);

        $reset = $input->getOption(self::INPUT_KEY_RESET);
        if ($reset) {
            $output->writeln('Reset the current position of migration to start from the beginning');
            $this->progress->reset();
        }

        if ($output->getVerbosity() > 1) {
            $this->logManager->process($this->verbosityLevels[$output->getVerbosity()]);
        } else {
            $this->logManager->process();
        }
    }
}
