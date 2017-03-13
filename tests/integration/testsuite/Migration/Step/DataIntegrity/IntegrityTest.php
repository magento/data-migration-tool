<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\DataIntegrity;

/**
 * Class IntegrityTest
 * @dbFixture data_integrity
 */
class IntegrityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $progress;

    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var \Migration\Step\DataIntegrity\Model\OrphanRecordsCheckerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkerFactory;

    /**
     * @var Integrity|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataIntegrity;

    /**
     * @return void
     */
    public function setUp()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();

        $this->config = $objectManager->get('\Migration\Config')
            ->init(dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $this->logger = $objectManager->create('Migration\Logger\Logger');
        $this->progress = $objectManager->create('Migration\App\ProgressBar\LogLevelProcessor');
        $this->source = $objectManager->create('Migration\ResourceModel\Source');
        $this->checkerFactory = $objectManager->create(
            'Migration\Step\DataIntegrity\Model\OrphanRecordsCheckerFactory'
        );

        $this->dataIntegrity = $this->getMockBuilder(Integrity::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDocumentList'])
            ->getMock();
        $this->setProperties($this->dataIntegrity, [
            'config' => $this->config,
            'logger' => $this->logger,
            'progress' => $this->progress,
            'source' => $this->source,
            'checkerFactory' => $this->checkerFactory
        ]);
    }

    /**
     * @param array $documents
     * @param bool $result
     * @param array $messages
     * @dataProvider documentsDataProvider
     * @return void
     */
    public function testPerform($documents, $result, $messages)
    {
        $this->dataIntegrity
            ->expects($this->any())
            ->method('getDocumentList')
            ->willReturn($documents);

        $this->assertEquals($result, $this->dataIntegrity->perform());
        $this->assertEquals($messages, \Migration\Logger\Logger::getMessages());
    }

    /**
     * Data provider for testPerform
     * @return array
     */
    public function documentsDataProvider()
    {
        return [
            [
                'documents' => ['eav_entity_type', 'eav_attribute'],
                'result' => true,
                'messages' => []
            ],
            [
                'documents' => ['eav_attribute_set'],
                'result' => false,
                'messages' => [
                    \Monolog\Logger::ERROR => [
                        'Foreign key (FK_EAV_ATTR_SET_ENTT_TYPE_ID_EAV_ENTT_TYPE_ENTT_TYPE_ID) constraint fails. ' .
                        'Orphan records id: 2,3 from `eav_attribute_set`.`entity_type_id` has no referenced records ' .
                        'in `eav_entity_type`'
                    ]
                ]
            ]
        ];
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $object
     * @param array $properties
     * @return void
     */
    private function setProperties($object, $properties = [])
    {
        $reflectionClass = new \ReflectionClass(get_class($object));
        foreach ($properties as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $reflectionProperty = $reflectionClass->getProperty($key);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $value);
            }
        }
    }
}
