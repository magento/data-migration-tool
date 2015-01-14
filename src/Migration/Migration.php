<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration;

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
     * @param \Magento\Framework\App\Console\Response $response
     */
    public function __construct(\Magento\Framework\App\Console\Response $response)
    {
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function launch()
    {
        return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(\Magento\Framework\App\Bootstrap $bootstrap, \Exception $exception)
    {
        return true;
    }
}
