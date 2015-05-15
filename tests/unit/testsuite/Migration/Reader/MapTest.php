<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

/**
 * Class MapTest
 */
class MapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Map
     */
    protected $map;

    public function setUp()
    {
        $this->map = new Map('tests/unit/testsuite/Migration/_files/map.xml');
    }

    public function testHasDocument()
    {
        $this->assertTrue($this->map->isDocumentMapped('source-document', MapInterface::TYPE_SOURCE));
        $this->assertFalse($this->map->isDocumentMapped('dest-document-ignored', MapInterface::TYPE_DEST));
        $this->assertFalse($this->map->isDocumentMapped('non-existent-document', MapInterface::TYPE_SOURCE));
    }

    public function testHasField()
    {
        $this->assertTrue($this->map->isFieldMapped('source-document', 'field2', MapInterface::TYPE_SOURCE));
        $this->assertFalse($this->map->isFieldMapped('dest-document', 'field-new', MapInterface::TYPE_DEST));

        $this->assertFalse(
            $this->map->isFieldMapped('document1', 'field-non-existent', MapInterface::TYPE_SOURCE)
        );
        $this->assertFalse($this->map->isFieldMapped('document1', 'field-non-existent', MapInterface::TYPE_DEST));
    }

    public function testGetDocumentMap()
    {
        $this->assertFalse($this->map->getDocumentMap('source-document-ignored', MapInterface::TYPE_SOURCE));
        $this->assertEquals('dest-document', $this->map->getDocumentMap(
            'source-document',
            MapInterface::TYPE_SOURCE
        ));
        $this->assertEquals(
            'document-non-existent',
            $this->map->getDocumentMap('document-non-existent', MapInterface::TYPE_SOURCE)
        );
        $this->assertEquals(
            'document-non-existent',
            $this->map->getDocumentMap('document-non-existent', MapInterface::TYPE_DEST)
        );
    }

    public function testGetFieldMap()
    {
        $this->assertFalse($this->map->getFieldMap('source-document', 'field1', MapInterface::TYPE_SOURCE));
        $this->assertEquals(
            'not-mapped-field',
            $this->map->getFieldMap('source-document', 'not-mapped-field', MapInterface::TYPE_SOURCE)
        );

        $this->assertEquals(
            'field2',
            $this->map->getFieldMap('source-document', 'field2', MapInterface::TYPE_SOURCE)
        );
        // Second run to check cached value
        $this->assertEquals(
            'field2',
            $this->map->getFieldMap('source-document', 'field2', MapInterface::TYPE_SOURCE)
        );
    }

    public function testGetFieldMapWithException()
    {
        $this->setExpectedException('Exception', 'Document has ambiguous configuration: source-document-ignored');
        $this->map->getFieldMap('source-document-ignored', 'field3', MapInterface::TYPE_SOURCE);
    }

    public function testGetFieldMapWithException2()
    {
        $this->setExpectedException('Exception', 'Document has ambiguous configuration: dest-document-ignored');
        $this->map->getFieldMap('source-document5', 'field3', MapInterface::TYPE_SOURCE);
    }

    public function testGetFieldMapWithException3()
    {
        $this->setExpectedException('Exception', 'Field has ambiguous configuration: dest-document5.field5');
        $this->map->getFieldMap('source-document5', 'field4', MapInterface::TYPE_SOURCE);
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
            $this->map->getHandlerConfig('source-document', 'field-with-handler', MapInterface::TYPE_SOURCE)
        );

        $this->assertEquals([], $this->map->getHandlerConfig(
            'source-document',
            'some-field',
            MapInterface::TYPE_SOURCE
        ));
    }

    public function testIsDocumentIgnoredSource()
    {
        $this->assertTrue($this->map->isDocumentIgnored('source-document-ignored', MapInterface::TYPE_SOURCE));
        $this->assertTrue($this->map->isDocumentIgnored('source-document-ignored-wc', MapInterface::TYPE_SOURCE));
        // Second run to check cached value
        $this->assertTrue($this->map->isDocumentIgnored('source-document-ignored', MapInterface::TYPE_SOURCE));
    }

    public function testIsDocumentIgnoredDest()
    {
        $this->assertTrue($this->map->isDocumentIgnored('dest-document-ignored', MapInterface::TYPE_DEST));
        $this->assertTrue($this->map->isDocumentIgnored('dest-document-ignored1', MapInterface::TYPE_DEST));
        // Second run to check cached value
        $this->assertTrue($this->map->isDocumentIgnored('dest-document-ignored', MapInterface::TYPE_DEST));
    }
}
