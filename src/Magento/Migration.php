<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Magento;

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
     * @var \Migration\Magento\App\Shell
     */
    protected $shellFactory;

    /**
     * @var string
     */
    protected $entryPoint;

    /**
     * @param \Magento\Framework\App\Console\Response $response
     * @param App\ShellFactory $shellFactory
     */
    public function __construct(
        \Magento\Framework\App\Console\Response $response,
        \Migration\Magento\App\ShellFactory $shellFactory,
        $entryPoint
    ) {
        $this->shellFactory = $shellFactory;
        $this->response = $response;
        $this->entryPoint = $entryPoint;
    }

    /**
     * {@inheritdoc}
     */
    public function launch()
    {
        $shell = $this->shellFactory->create(['entryPoint' => $this->entryPoint]);
        $shell->run();
        return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(\Magento\Framework\App\Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}
