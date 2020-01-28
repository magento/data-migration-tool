<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Migration\Logger\Manager;
use Migration\Config;
use Migration\App\Progress;
use Migration\Exception;

/**
 * Class AbstractMigrateCommand
 */
class AbstractMigrateCommand extends Command
{
    /**#@+
     * Keys and shortcuts for input arguments and options
     */
    const INPUT_KEY_CONFIG = 'config';
    const INPUT_KEY_RESET = 'reset';
    const INPUT_KEY_AUTO = 'auto';
    /**#@-*/

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Manager
     */
    protected $logManager;

    /**
     * @var Progress
     */
    protected $progress;

    /**
     * Verbosity levels
     *
     * @var array
     */
    protected $verbosityLevels = [
        OutputInterface::VERBOSITY_QUIET => Manager::LOG_LEVEL_ERROR,
        OutputInterface::VERBOSITY_NORMAL => Manager::LOG_LEVEL_INFO,
        OutputInterface::VERBOSITY_VERBOSE => Manager::LOG_LEVEL_INFO,
        OutputInterface::VERBOSITY_VERY_VERBOSE => Manager::LOG_LEVEL_DEBUG,
        OutputInterface::VERBOSITY_DEBUG => Manager::LOG_LEVEL_DEBUG
    ];

    /**
     * @param Config $config
     * @param Manager $logManager
     * @param Progress $progress
     */
    public function __construct(
        Config $config,
        Manager $logManager,
        Progress $progress
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
                InputArgument::REQUIRED,
                'Path to main configuration file, i.e.: etc/m1_version/config.xml'
            ),
            new InputOption(
                self::INPUT_KEY_RESET,
                'r',
                InputOption::VALUE_NONE,
                'Reset the current position of migration to start from the beginning'
            ),
            new InputOption(
                self::INPUT_KEY_AUTO,
                'a',
                InputOption::VALUE_NONE,
                'Automatically resolve issues on the way of migration'
            ),
        ]);
        parent::configure();
    }

    /**
     * Initialize
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws Exception
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {

        $config = $input->getArgument(self::INPUT_KEY_CONFIG);
        $this->config->init($config);

        $reset = $input->getOption(self::INPUT_KEY_RESET);
        if ($reset && $this->canReset()) {
            $output->writeln('Reset the current position of migration to start from the beginning');
            $this->progress->reset();
        }

        $autoResolve = $input->getOption(self::INPUT_KEY_AUTO);
        if ($autoResolve) {
            $this->config->setOption(Config::OPTION_AUTO_RESOLVE, 1);
        }

        if ($output->getVerbosity() > 1) {
            $this->logManager->process($this->verbosityLevels[$output->getVerbosity()]);
        } else {
            $this->logManager->process();
        }
    }

    /**
     * @return bool
     */
    protected function canReset()
    {
        return true;
    }
}
