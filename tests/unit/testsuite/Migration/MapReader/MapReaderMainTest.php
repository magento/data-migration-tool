<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\MapReader;

use Migration\MapReaderInterface;

/**
 * Class MapReaderTest
 */
class MapReaderMainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MapReaderEav
     */
    protected $map;

    public function setUp()
    {
        /** @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject $config */
        $config = $this->getMockBuilder('Migration\Config')->disableOriginalConstructor()
            ->setMethods(['getOption'])->getMock();
        $config->expects($this->once())->method('getOption')->with('map_file')->will(
            $this->returnValue('tests/unit/testsuite/Migration/_files/map.xml')
        );
        $this->map = new MapReaderMain($config);
    }

    public function testHasDocument()
    {
        $this->assertTrue($this->map->isDocumentMaped('source-document', MapReaderInterface::TYPE_SOURCE));
        $this->assertFalse($this->map->isDocumentMaped('dest-document-ignored', MapReaderInterface::TYPE_DEST));
        $this->assertFalse($this->map->isDocumentMaped('non-existent-document', MapReaderInterface::TYPE_SOURCE));
    }

    public function testHasField()
    {
        $this->assertTrue($this->map->isFieldMapped('source-document', 'field2', MapReaderInterface::TYPE_SOURCE));
        $this->assertFalse($this->map->isFieldMapped('dest-document', 'field-new', MapReaderInterface::TYPE_DEST));

        $this->assertFalse(
            $this->map->isFieldMapped('document1', 'field-non-existent', MapReaderInterface::TYPE_SOURCE)
        );
        $this->assertFalse($this->map->isFieldMapped('document1', 'field-non-existent', MapReaderInterface::TYPE_DEST));
    }

    public function testGetDocumentMap()
    {
        $this->assertFalse($this->map->getDocumentMap('source-document-ignored', MapReaderInterface::TYPE_SOURCE));
        $this->assertEquals('dest-document', $this->map->getDocumentMap(
            'source-document',
            MapReaderInterface::TYPE_SOURCE
        ));
        $this->assertEquals(
            'document-non-existent',
            $this->map->getDocumentMap('document-non-existent', MapReaderInterface::TYPE_SOURCE)
        );
        $this->assertEquals(
            'document-non-existent',
            $this->map->getDocumentMap('document-non-existent', MapReaderInterface::TYPE_DEST)
        );
    }

    public function testGetFieldMap()
    {
        $this->assertFalse($this->map->getFieldMap('source-document', 'field1', MapReaderInterface::TYPE_SOURCE));
        $this->assertEquals(
            'not-mapped-field',
            $this->map->getFieldMap('source-document', 'not-mapped-field', MapReaderInterface::TYPE_SOURCE)
        );

        $this->assertEquals(
            'field2',
            $this->map->getFieldMap('source-document', 'field2', MapReaderInterface::TYPE_SOURCE)
        );
        // Second run to check cached value
        $this->assertEquals(
            'field2',
            $this->map->getFieldMap('source-document', 'field2', MapReaderInterface::TYPE_SOURCE)
        );
    }

    public function testGetFieldMapWithException()
    {
        $this->setExpectedException('Exception', 'Document has ambiguous configuration: source-document-ignored');
        $this->map->getFieldMap('source-document-ignored', 'field3', MapReaderInterface::TYPE_SOURCE);
    }

    public function testGetFieldMapWithException2()
    {
        $this->setExpectedException('Exception', 'Document has ambiguous configuration: dest-document-ignored');
        $this->map->getFieldMap('source-document5', 'field3', MapReaderInterface::TYPE_SOURCE);
    }

    public function testGetFieldMapWithException3()
    {
        $this->setExpectedException('Exception', 'Field has ambiguous configuration: dest-document5.field5');
        $this->map->getFieldMap('source-document5', 'field4', MapReaderInterface::TYPE_SOURCE);
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
            $this->map->getHandlerConfig('source-document', 'field-with-handler', MapReaderInterface::TYPE_SOURCE)
        );

        $this->assertEquals([], $this->map->getHandlerConfig(
            'source-document',
            'some-field',
            MapReaderInterface::TYPE_SOURCE
        ));
    }

    public function testIsDocumentIgnoredSource()
    {
        $this->assertTrue($this->map->isDocumentIgnored('source-document-ignored', MapReaderInterface::TYPE_SOURCE));
        $this->assertTrue($this->map->isDocumentIgnored('source-document-ignored-wc', MapReaderInterface::TYPE_SOURCE));
        // Second run to check cached value
        $this->assertTrue($this->map->isDocumentIgnored('source-document-ignored', MapReaderInterface::TYPE_SOURCE));
    }

    public function testIsDocumentIgnoredDest()
    {
        $this->assertTrue($this->map->isDocumentIgnored('dest-document-ignored', MapReaderInterface::TYPE_DEST));
        $this->assertTrue($this->map->isDocumentIgnored('dest-document-ignored1', MapReaderInterface::TYPE_DEST));
        // Second run to check cached value
        $this->assertTrue($this->map->isDocumentIgnored('dest-document-ignored', MapReaderInterface::TYPE_DEST));
    }
}
