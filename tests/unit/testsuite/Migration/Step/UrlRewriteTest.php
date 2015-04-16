<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

/**
 * Class UrlRewriteTest
 */
class UrlRewriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\UrlRewrite
     */
    protected $urlRewrite;

    /**
     * @var \Migration\Step\Progress|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\Step\UrlRewrite\VersionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $versionFactory;

    /**
     * @var \Migration\Step\UrlRewrite\Version11410to2000|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $version;

    public function setUp()
    {
        $this->progress = $this->getMockBuilder('\Migration\Step\Progress')
            ->setMethods(['getProgress', 'getMaxSteps', 'advance', 'finish', 'setStep', 'reset', 'start'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMock('\Migration\Logger\Logger', ['debug', 'error'], [], '', false);
        $this->config = $this->getMock('\Migration\Config', [], [], '', false);

        $this->versionFactory = $this->getMock('\Migration\Step\UrlRewrite\VersionFactory', [], [], '', false);
        $this->version = $this->getMock('\Migration\Step\UrlRewrite\Version191to2000', [], [], '', false);
        $this->versionFactory->expects($this->any())->method('create')->willReturn($this->version);
        $this->config->expects($this->any())->method('getSource')->willReturn([
            'type' => 'database',
            'version' => '1.14.1.0'
        ]);
        $this->urlRewrite = new UrlRewrite($this->config, $this->versionFactory);
    }

    public function testRun()
    {
        $this->version->expects($this->atLeastOnce())->method('run');
        $this->urlRewrite->run();
    }

    public function testIntegrity()
    {
        $this->version->expects($this->atLeastOnce())->method('integrity');
        $this->urlRewrite->integrity();
    }

    /**
     * @param string $sourceType
     * @param bool $expected
     * @dataProvider canStartDataProvider
     */
    public function testCanStart($sourceType, $expected)
    {
        $this->config = $this->getMock('\Migration\Config', [], [], '', false);
        $this->config->expects($this->any())->method('getSource')->willReturn([
            'type' => $sourceType,
            'version' => '1.14.1.0'
        ]);
        if ($expected) {
            $this->setExpectedException('\Exception', 'Can not execute step');
        }
        $this->urlRewrite = new UrlRewrite($this->config, $this->versionFactory);
    }

    public function canStartDataProvider()
    {
        return [
            ['database', false],
            ['file', true]
        ];
    }
}
