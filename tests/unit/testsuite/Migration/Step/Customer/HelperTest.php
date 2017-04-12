<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace unit\testsuite\Migration\Step\Customer;

use Migration\ResourceModel\Structure;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Record\Collection;
use Migration\Step\Customer\Helper;

/**
 * Class HelperTest
 * @SuppressWarnings(PHPMD)
 */
class HelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configReader;

    /**
     * @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerAttributes;

    /**
     * @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerGroups;

    /**
     * @var \Migration\ResourceModel\Adapter\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sourceAdapter;

    /**
     * @var \Migration\ResourceModel\Adapter\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destAdapter;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->sourceAdapter = $this->getMockBuilder(\Migration\ResourceModel\Adapter\Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods(['fetchAll', 'getSelect'])
            ->getMock();
        $this->destAdapter = $this->getMockBuilder(\Migration\ResourceModel\Adapter\Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDocumentStructure'])
            ->getMock();

        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['from', 'join', 'where', 'union', 'getAdapter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->select->expects($this->any())->method('from')->willReturnSelf();
        $this->select->expects($this->any())->method('join')->willReturnSelf();
        $this->select->expects($this->any())->method('where')->willReturnSelf();
        $this->select->expects($this->any())->method('union')->willReturnSelf();
        $this->select->expects($this->any())->method('getAdapter')->willReturn($this->sourceAdapter);
        $this->sourceAdapter->expects($this->any())->method('getSelect')->willReturn($this->select);

        $this->source = $this->getMockBuilder(\Migration\ResourceModel\Source::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdapter', 'addDocumentPrefix'])
            ->getMock();
        $this->source->expects($this->any())->method('getAdapter')->willReturn($this->sourceAdapter);
        $this->source->expects($this->any())->method('addDocumentPrefix')->willReturnArgument(0);

        $this->destination = $this->getMockBuilder(\Migration\ResourceModel\Destination::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdapter'])
            ->getMock();
        $this->destination->expects($this->any())->method('getAdapter')->willReturn($this->destAdapter);

        $this->configReader = $this->getMockBuilder(\Migration\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->readerAttributes = $this->getMockBuilder(\Migration\Reader\Groups::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->readerGroups = $this->getMockBuilder(\Migration\Reader\Groups::class)
            ->setMethods(['getGroup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerGroups->expects($this->at(0))
            ->method('getGroup')
            ->with('source_documents')
            ->willReturn([
                'customer_entity' => 'entity_id',
                'customer_address_entity' => 'entity_id'
            ]);

        $groupsFactory = $this->getMockBuilder(\Migration\Reader\GroupsFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $groupsFactory->expects($this->at(0))
            ->method('create')
            ->with('customer_attribute_groups_file')
            ->willReturn($this->readerAttributes);
        $groupsFactory->expects($this->at(1))
            ->method('create')
            ->with('customer_document_groups_file')
            ->willReturn($this->readerGroups);

        $this->helper = $this->getMockBuilder(Helper::class)
            ->setConstructorArgs([$this->source, $this->destination, $groupsFactory, $this->configReader])
            ->setMethods(['getAttributesData'])
            ->getMock();
    }

    /**
     * @param array $testMethodArguments
     * @param array $recordsData
     * @param array $attributesValues
     * @param array $upgradePasswordHash
     * @param array $recordsResult
     *
     * @dataProvider fixturesDataProvider
     * @return void
     */
    public function testUpdateAttributeData(
        $testMethodArguments,
        $recordsData,
        $attributesValues,
        $upgradePasswordHash,
        $recordsResult
    ) {
        $records = [];
        foreach ($recordsData as $recordData) {
            $records[] = $this->getMockBuilder(Record::class)
                ->setConstructorArgs([$recordData])
                ->setMethods(null)
                ->getMock();
        }
        $structure = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $destinationRecords = new Collection($structure, $records);

        $this->initMaps();

        $this->sourceAdapter
            ->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn($attributesValues);

        $this->configReader
            ->expects($this->any())
            ->method('getOption')
            ->willReturnMap([$upgradePasswordHash]);

        $this->helper->updateAttributeData(
            $testMethodArguments['entityTypeCode'],
            $testMethodArguments['sourceDocName'],
            $testMethodArguments['destinationDocName'],
            $destinationRecords
        );

        foreach ($destinationRecords as $key => $record) {
            $this->assertEquals($record->getData(), $recordsResult[$key]);
        }
    }

    /**
     * Initializes Mocks return maps
     *
     * @return void
     */
    private function initMaps()
    {
        $this->helper
            ->expects($this->any())
            ->method('getAttributesData')
            ->willReturnMap([
                [
                    'customer',
                    [
                        'password_hash' => [
                            'attribute_id' => '12',
                            'backend_type' => 'varchar',
                            'attribute_code' => 'password_hash',
                            'entity_type_id' => '1'
                        ],
                    ]
                ], [
                    'customer_address',
                    [
                        'company' => [
                            'attribute_id' => '13',
                            'backend_type' => 'varchar',
                            'attribute_code' => 'company',
                            'entity_type_id' => '2'
                        ],
                        'country_id' => [
                            'attribute_id' => '14',
                            'backend_type' => 'varchar',
                            'attribute_code' => 'country_id',
                            'entity_type_id' => '2'
                        ],
                    ]
                ]
            ]);
        $this->readerAttributes
            ->expects($this->any())
            ->method('getGroup')
            ->willReturnMap([
                ['customer_entity', ['entity_id' => '', 'entity_type_id' => '', 'email' => '', 'password_hash' => '']],
                ['customer_address_entity', ['entity_id' => '', 'city' => '', 'company' => '', 'country_id' => '']]
            ]);
        $this->destAdapter
            ->expects($this->any())
            ->method('getDocumentStructure')
            ->willReturnMap([
                [
                    'customer_entity',
                    [
                        'entity_id' => ['DEFAULT' => null, 'NULLABLE' => false],
                        'entity_type_id' => ['DEFAULT' => null, 'NULLABLE' => false],
                        'email' => ['DEFAULT' => null, 'NULLABLE' => true],
                        'password_hash' => ['DEFAULT' => null, 'NULLABLE' => false],
                    ]
                ], [
                    'customer_address_entity',
                    [
                        'entity_id' => ['DEFAULT' => null, 'NULLABLE' => false],
                        'city' => ['DEFAULT' => null, 'NULLABLE' => false],
                        'company' => ['DEFAULT' => null, 'NULLABLE' => true],
                        'country_id' => ['DEFAULT' => null, 'NULLABLE' => false],
                    ]
                ]
            ]);
    }

    /**
     * @return array
     */
    public function fixturesDataProvider()
    {
        return [
            [
                'testMethodArguments' => [
                    'entityTypeCode' => 'customer',
                    'sourceDocName' => 'customer_entity',
                    'destinationDocName' => 'customer_entity'
                ],
                'recordsData' => [
                    ['entity_id' => '1', 'entity_type_id' => '1', 'email' => 'customer1@example.com'],
                    ['entity_id' => '2', 'entity_type_id' => '1', 'email' => 'customer2@example.com'],
                    ['entity_id' => '3', 'entity_type_id' => '1', 'email' => 'customer3@example.com'],
                    ['entity_id' => '4', 'entity_type_id' => '1', 'email' => 'customer4@example.com']
                ],
                'attributesValues' => [
                    [
                        'entity_id' => '1',
                        'attribute_id' => '12',
                        'value' => '34356a3d028accfb3c2996827b706bf5:UmPvGtih25eQCjC5f6NMwqkds500x2Jd'
                    ], [
                        'entity_id' => '2',
                        'attribute_id' => '12',
                        'value' => '86a375aacb17606c185d31c8d3e320ce'
                    ], [
                        'entity_id' => '3',
                        'attribute_id' => '12',
                        'value' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855'
                    ], [
                        'entity_id' => '4',
                        'attribute_id' => '12',
                        'value' => '123123q:UmPvGtih25eQCjC5f6NMwqkds500x2Jd'
                    ]
                ],
                'upgradePasswordHash' => [Helper::UPGRADE_CUSTOMER_PASSWORD_HASH, true],
                'recordsResult' => [
                    [
                        'entity_id' => '1',
                        'entity_type_id' => '1',
                        'email' => 'customer1@example.com',
                        'password_hash' => '34356a3d028accfb3c2996827b706bf5:UmPvGtih25eQCjC5f6NMwqkds500x2Jd:0'
                    ], [
                        'entity_id' => '2',
                        'entity_type_id' => '1',
                        'email' => 'customer2@example.com',
                        'password_hash' => '86a375aacb17606c185d31c8d3e320ce::0'
                    ], [
                        'entity_id' => '3',
                        'entity_type_id' => '1',
                        'email' => 'customer3@example.com',
                        'password_hash' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855::1'
                    ], [
                        'entity_id' => '4',
                        'entity_type_id' => '1',
                        'email' => 'customer4@example.com',
                        'password_hash' => '123123q:UmPvGtih25eQCjC5f6NMwqkds500x2Jd'
                    ]
                ],
            ], [
                'testMethodArguments' => [
                    'entityTypeCode' => 'customer',
                    'sourceDocName' => 'customer_entity',
                    'destinationDocName' => 'customer_entity'
                ],
                'recordsData' => [
                    ['entity_id' => '1', 'entity_type_id' => '1', 'email' => 'customer1@example.com'],
                    ['entity_id' => '2', 'entity_type_id' => '1', 'email' => 'customer2@example.com']
                ],
                'attributesValues' => [
                    [
                        'entity_id' => '1',
                        'attribute_id' => '12',
                        'value' => '34356a3d028accfb3c2996827b706bf5:UmPvGtih25eQCjC5f6NMwqkds500x2Jd'
                    ],
                    [
                        'entity_id' => '2',
                        'attribute_id' => '12',
                        'value' => '123123q:UmPvGtih25eQCjC5f6NMwqkds500x2Jd'
                    ]
                ],
                'upgradePasswordHash' => [Helper::UPGRADE_CUSTOMER_PASSWORD_HASH, false],
                'recordsResult' => [
                    [
                        'entity_id' => '1',
                        'entity_type_id' => '1',
                        'email' => 'customer1@example.com',
                        'password_hash' => '34356a3d028accfb3c2996827b706bf5:UmPvGtih25eQCjC5f6NMwqkds500x2Jd'
                    ],
                    [
                        'entity_id' => '2',
                        'entity_type_id' => '1',
                        'email' => 'customer2@example.com',
                        'password_hash' => '123123q:UmPvGtih25eQCjC5f6NMwqkds500x2Jd'
                    ]
                ],
            ], [
                'testMethodArguments' => [
                    'entityTypeCode' => 'customer_address',
                    'sourceDocName' => 'customer_address_entity',
                    'destinationDocName' => 'customer_address_entity'
                ],
                'recordsData' => [
                    ['entity_id' => '1', 'city' => 'Austin'],
                    ['entity_id' => '2', 'city' => 'Kiev']
                ],
                'attributesValues' => [
                    [
                        'entity_id' => '1',
                        'attribute_id' => '13',
                        'value' => 'Company Name'
                    ]
                ],
                'upgradePasswordHash' => [Helper::UPGRADE_CUSTOMER_PASSWORD_HASH, false],
                'recordsResult' => [
                    [
                        'entity_id' => '1',
                        'city' => 'Austin',
                        'company' => 'Company Name',
                        'country_id' => ''
                    ], [
                        'entity_id' => '2',
                        'city' => 'Kiev',
                        'company' => null,
                        'country_id' => ''
                    ]
                ],
            ]
        ];
    }
}
