<?php
/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
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
    }
}
