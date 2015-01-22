<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

/**
 * Class Shell
 */
class Shell extends \Magento\Framework\App\AbstractShell
{
    /**
     * @var \Migration\Config
     */
    protected $config;

    /**
     * @var \Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Migration\Logger\Writer\Console
     */
    protected $consoleLogWriter;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Migration\Config $config
     * @param \Migration\Logger\Logger $logger
     * @param \Migration\Logger\Writer\Console $consoleWriter
     * @param string $entryPoint
     * @throws \Exception
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Migration\Config $config,
        \Migration\Logger\Logger $logger,
        \Migration\Logger\Writer\Console $consoleWriter,
        $entryPoint
    ) {
        $this->logger = $logger;
        $this->consoleLogWriter = $consoleWriter;
        parent::__construct($filesystem, $entryPoint);
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if ($this->_showHelp()) {
            return $this;
        }

        $this->logger->addWriter($this->consoleLogWriter);
        $verbose = $this->getArg('verbose');
        if ($verbose) {
            if ($this->logger->isLogLevelValid($verbose)) {
                $this->consoleLogWriter->setLoggingLevel($verbose);
            } else {
                $this->logger->logError("Invalid verbosity level provided!");
                return;
            }
        }

        if ($this->getArg('config')) {
            $this->logger->logInfo('Loaded custom config file: ' . $this->getArg('config'));
            $this->config->init($this->getArg('config'));
        } else {
            $this->logger->logInfo('Loaded default config file: ' . $this->getArg('config'));
            $this->config->init();
        }

        if ($this->getArg('type')) {
            $this->logger->logInfo($this->getArg('type'));
        }

        /**
         * @TODO: call Step Manager
         */

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsageHelp()
    {
        return <<<USAGE
Usage:  php -f {$this->_entryPoint} -- [options]

  --config <value>    Path to main configuration file
  --type <value>      Type of operation: migration or delta delivery
  --verbose <level>   Verbosity levels: DEBUG, INFO, NONE
  help              This help

USAGE;
    }
}
