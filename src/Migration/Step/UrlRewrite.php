<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

/**
 * Class UrlRewrite
 */
class UrlRewrite extends DatabaseStep
{
    /**
     * @var \Migration\Step\StepInterface
     */
    protected $rewriteVersion;

    /**
     * @param \Migration\Config $config
     * @param \Migration\Step\UrlRewrite\VersionFactory $versionFactory
     */
    public function __construct(
        \Migration\Config $config,
        \Migration\Step\UrlRewrite\VersionFactory $versionFactory
    ) {
        $this->rewriteVersion = $versionFactory->create(
            $config->getSource()['version'],
            $config->getDestination()['version']
        );
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->rewriteVersion->run();
    }

    /**
     * {@inheritdoc}
     */
    public function integrity()
    {
        return $this->rewriteVersion->integrity();
    }

    /**
     * {@inheritdoc}
     */
    public function volumeCheck()
    {
        return $this->rewriteVersion->volumeCheck();
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->rewriteVersion->getTitle();
    }
}
