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

    public function setUp()
    {
        $this->map = new MapReader();
        $this->map->init(__DIR__ . '/_files/map.xml');
    }

    public function testInitDefaultMapFile()
    {
        $defaultConfigFile = realpath(__DIR__ . '/../../../..') . '/etc/map.xml';
        if (!file_exists($defaultConfigFile)) {
            $this->setExpectedException('Exception', 'Invalid map filename: ' . $defaultConfigFile);
        }
        $config = new MapReader();
        $config->init();
    }

    public function testInitBadFile()
    {
        $badFileName = __DIR__ . '/_files/map-bad.xml';
        $this->setExpectedException('Exception', 'Invalid map filename: ' . $badFileName);
        $this->map = new MapReader();
        $this->map->init($badFileName);
    }

    public function testInitNotValidFile()
    {
        $invalidFileName = __DIR__ . '/_files/map-invalid.xml';
        $this->setExpectedException('Exception', 'XML file is invalid.');
        $this->map = new MapReader();
        $this->map->init($invalidFileName);
    }

    public function testHasDocument()
    {
        $this->assertTrue($this->map->hasDocument('source-document'));
        $this->assertTrue($this->map->hasDocument('dest-document-ignored', MapReader::TYPE_DEST));
        $this->assertFalse($this->map->hasDocument('non-existent-document'));
    }

    public function testHasField()
    {
        $this->assertTrue($this->map->hasField('source-document', 'field1'));
        $this->assertTrue($this->map->hasField('dest-document', 'field-new', MapReader::TYPE_DEST));

        $this->assertFalse($this->map->hasField('document1', 'field-non-existent'));
        $this->assertFalse($this->map->hasField('document1', 'field-non-existent', MapReader::TYPE_DEST));
    }

    public function testGetDocumentMap()
    {
        $this->assertEquals(['source-document' => 'dest-document'], $this->map->getDocumentMap('source-document'));

        $this->assertEquals(
            ['source-document-ignored' => '@ignored'],
            $this->map->getDocumentMap('source-document-ignored')
        );

        $this->assertEquals(
            ['dest-document-ignored' => '@ignored'],
            $this->map->getDocumentMap('dest-document-ignored', MapReader::TYPE_DEST)
        );

        $this->assertEquals([], $this->map->getDocumentMap('document-non-existent'));
        $this->assertEquals([], $this->map->getDocumentMap('document-non-existent', MapReader::TYPE_DEST));
    }

    public function testGetFieldMap()
    {
        $this->assertEquals(
            ['source-document::field1' => '@ignored'],
            $this->map->getFieldMap('source-document', 'field1')
        );

        $this->assertEquals(
            ['source-document::field2' => 'dest-document::field2'],
            $this->map->getFieldMap('source-document', 'field2')
        );
    }

    public function testGetHandlerConfig()
    {
        $handlerConfig = [
            'class' => '\Migration\Handler\SetValue',
            'params' => [
                'default_value' => 10
            ]
        ];

        $this->assertEquals($handlerConfig, $this->map->getHandlerConfig('source-document', 'field-with-handler'));
    }
}
