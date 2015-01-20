<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

class Shell extends \Magento\Framework\App\AbstractShell
{
    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $entryPoint
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        $entryPoint
    ) {
        parent::__construct($filesystem, $entryPoint);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if ($this->_showHelp()) {
            return $this;
        }

        if ($this->getArg('config')) {
            \Zend_Debug::dump($this->getArg('config'), 'config');
        }
        if ($this->getArg('type')) {
            \Zend_Debug::dump($this->getArg('type'), 'type');
        }
        if ($this->getArg('help')) {
            echo $this->getUsageHelp();
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