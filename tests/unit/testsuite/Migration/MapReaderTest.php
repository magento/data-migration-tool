<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration;

/**
 * Class MapReaderTest
 */
class MapReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MapReader
     */
    protected $map;

    /**
     * @var string
     */
    protected $rootDir;

    public function setUp()
    {
        $this->rootDir = dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR;
        $config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()
            ->setMethods(['getOption'])
            ->getMock();
        $config->expects($this->once())->method('getOption')
            ->with('map_file')->will($this->returnValue('tests/unit/testsuite/Migration/_files/map.xml'));
        $this->map = new MapReader($config);
    }

    public function testReinnitConfig()
    {
        $this->assertSame($this->map, $this->map->init());
    }

    public function testInitBadFile()
    {
        $badFileName = 'tests/unit/testsuite/Migration/_files/map-bad.xml';
        $config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()
            ->setMethods(['getOption'])
            ->getMock();
        $config->expects($this->once())->method('getOption')
            ->with('map_file')->will($this->returnValue($badFileName));

        $this->setExpectedException('Exception', 'Invalid map filename: ' . $this->rootDir .$badFileName);
        $map = new MapReader($config);
        $map->init();
    }

    public function testInitNotValidFile()
    {
        $invalidFileName = 'tests/unit/testsuite/Migration/_files/map-invalid.xml';
        $config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()
            ->setMethods(['getOption'])
            ->getMock();
        $config->expects($this->once())->method('getOption')
            ->with('map_file')->will($this->returnValue($invalidFileName));

        $this->setExpectedException('Exception', 'XML file is invalid.');
        $map = new MapReader($config);
        $map->init();
    }

    public function testHasDocument()
    {
        $this->assertTrue($this->map->isDocumentMaped('source-document', MapReader::TYPE_SOURCE));
        $this->assertFalse($this->map->isDocumentMaped('dest-document-ignored', MapReader::TYPE_DEST));
        $this->assertFalse($this->map->isDocumentMaped('non-existent-document', MapReader::TYPE_SOURCE));
    }

    public function testHasField()
    {
        $this->assertTrue($this->map->isFieldMapped('source-document', 'field2', MapReader::TYPE_SOURCE));
        $this->assertFalse($this->map->isFieldMapped('dest-document', 'field-new', MapReader::TYPE_DEST));

        $this->assertFalse($this->map->isFieldMapped('document1', 'field-non-existent', MapReader::TYPE_SOURCE));
        $this->assertFalse($this->map->isFieldMapped('document1', 'field-non-existent', MapReader::TYPE_DEST));
    }

    public function testGetDocumentMap()
    {
        $this->assertFalse($this->map->getDocumentMap('source-document-ignored', MapReader::TYPE_SOURCE));
        $this->assertEquals('dest-document', $this->map->getDocumentMap('source-document', MapReader::TYPE_SOURCE));
        $this->assertEquals(
            'document-non-existent',
            $this->map->getDocumentMap('document-non-existent', MapReader::TYPE_SOURCE)
        );
        $this->assertEquals(
            'document-non-existent',
            $this->map->getDocumentMap('document-non-existent', MapReader::TYPE_DEST)
        );
    }

    public function testGetFieldMap()
    {
        $this->assertFalse($this->map->getFieldMap('source-document', 'field1', MapReader::TYPE_SOURCE));
        $this->assertEquals(
            'not-mapped-field',
            $this->map->getFieldMap('source-document', 'not-mapped-field', MapReader::TYPE_SOURCE)
        );

        $this->assertEquals(
            'field2',
            $this->map->getFieldMap('source-document', 'field2', MapReader::TYPE_SOURCE)
        );
    }

    public function testGetFieldMapWithException()
    {
        $this->setExpectedException('Exception', 'Document has ambiguous configuration: source-document-ignored');
        $this->map->getFieldMap('source-document-ignored', 'field3', MapReader::TYPE_SOURCE);
    }

    public function testGetFieldMapWithException2()
    {
        $this->setExpectedException('Exception', 'Document has ambiguous configuration: dest-document-ignored');
        $this->map->getFieldMap('source-document5', 'field3', MapReader::TYPE_SOURCE);
    }

    public function testGetFieldMapWithException3()
    {
        $this->setExpectedException('Exception', 'Field has ambiguous configuration: dest-document5::field5');
        $this->map->getFieldMap('source-document5', 'field4', MapReader::TYPE_SOURCE);
    }

    public function testGetHandlerConfig()
    {
        $handlerConfig = [
            'class' => '\Migration\Handler\SetValue',
            'params' => [
                'default_value' => 10
            ]
        ];

        $this->assertEquals(
            $handlerConfig,
            $this->map->getHandlerConfig('source-document', 'field-with-handler', MapReader::TYPE_SOURCE)
        );

        $this->assertEquals([], $this->map->getHandlerConfig('source-document', 'some-field', MapReader::TYPE_SOURCE));
    }

    public function testValidateType()
    {
        $this->setExpectedException('Exception', 'Unknown resource type: badType');
        $this->map->getOppositeType('badType');
    }
    public function testIsDocumentIgnoredSource()
    {
        $this->map->isDocumentIgnored('source-document-ignored1', MapReader::TYPE_SOURCE);
        $this->map->isDocumentIgnored('source-document-ignored1', MapReader::TYPE_SOURCE);
    }

    public function testIsDocumentIgnoredDest()
    {
        $this->map->isDocumentIgnored('dest-document-ignored1', MapReader::TYPE_DEST);
        $this->map->isDocumentIgnored('dest-document-ignored1', MapReader::TYPE_DEST);
    }
}
