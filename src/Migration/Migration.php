<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration;

use Migration\App\Shell;

/**
 * Launches the application
 */
class Migration implements \Magento\Framework\AppInterface
{
    /**
     * @var \Magento\Framework\App\Console\Response
     */
    protected $response;

    /**
     * @var \Migration\App\ShellFactory
     */
    protected $shellFactory;

    /**
     * @var string
     */
    protected $entryPoint;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteFactory
     */
    protected $directoryWriteFactory;

    /**
     * @param \Magento\Framework\App\Console\Response $response
     * @param App\ShellFactory $shellFactory
     * @param \Magento\Framework\Filesystem\Directory\WriteFactory $directoryWriteFactory
     * @param string $entryPoint
     */
    public function __construct(
        \Magento\Framework\App\Console\Response $response,
        \Migration\App\ShellFactory $shellFactory,
        \Magento\Framework\Filesystem\Directory\WriteFactory $directoryWriteFactory,
        $entryPoint
    ) {
        $this->directoryWriteFactory = $directoryWriteFactory;
        $this->shellFactory = $shellFactory;
        $this->response = $response;
        $this->entryPoint = $entryPoint;
    }

    /**
     * {@inheritdoc}
     */
    public function launch()
    {
        $this->createDirectories();
        /** @var Shell $shell */
        $shell = $this->shellFactory->create(['entryPoint' => $this->entryPoint]);
        try {
            $shell->run();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $this->response;
    }

    /**
     * Create required directories
     *
     * @return void
     */
    protected function createDirectories()
    {
        $directories = [
            dirname(dirname(__DIR__)) . '/var'
        ];
        foreach ($directories as $path) {
            $writer = $this->directoryWriteFactory->create($path);
            if (!$writer->isDirectory()) {
                $writer->create();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(\Magento\Framework\App\Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}
