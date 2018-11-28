<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

/**
 * Class MapTest
 */
class MapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Map
     */
    protected $map;

    /**
     * @return void
     */
    public function setUp()
    {
        $validationState = $this->getMockBuilder(\Magento\Framework\App\Arguments\ValidationState::class)
            ->disableOriginalConstructor()
            ->setMethods(['isValidationRequired'])
            ->getMock();

        $validationState->expects($this->any())->method('isValidationRequired')->willReturn(true);

        $this->map = new Map($validationState, 'tests/unit/testsuite/Migration/_files/map.xml');
    }

    /**
     * @return void
     */
    public function testHasDocument()
    {
        $this->assertTrue($this->map->isDocumentMapped('source-document', MapInterface::TYPE_SOURCE));
        $this->assertFalse($this->map->isDocumentMapped('dest-document-ignored', MapInterface::TYPE_DEST));
        $this->assertFalse($this->map->isDocumentMapped('non-existent-document', MapInterface::TYPE_SOURCE));
    }

    /**
     * @return void
     */
    public function testHasField()
    {
        $this->assertTrue($this->map->isFieldMapped('source-document', 'field2', MapInterface::TYPE_SOURCE));
        $this->assertFalse($this->map->isFieldMapped('dest-document', 'field-new', MapInterface::TYPE_DEST));

        $this->assertFalse(
            $this->map->isFieldMapped('document1', 'field-non-existent', MapInterface::TYPE_SOURCE)
        );
        $this->assertFalse($this->map->isFieldMapped('document1', 'field-non-existent', MapInterface::TYPE_DEST));
    }

    /**
     * @return void
     */
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

    /**
     * @throws \Migration\Exception
     * @return void
     */
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

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testGetFieldMapWithException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Document has ambiguous configuration: source-document-ignored');
        $this->map->getFieldMap('source-document-ignored', 'field3', MapInterface::TYPE_SOURCE);
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testGetFieldMapWithException2()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Document has ambiguous configuration: dest-document-ignored');
        $this->map->getFieldMap('source-document5', 'field3', MapInterface::TYPE_SOURCE);
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testGetFieldMapWithException3()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Field has ambiguous configuration: dest-document5.field5');
        $this->map->getFieldMap('source-document5', 'field4', MapInterface::TYPE_SOURCE);
    }

    /**
     * @return void
     */
    public function testGetHandlerConfigs()
    {
        $handlerConfig = [
            [
                'class' => \Migration\Handler\SetValue::class,
                'params' => [
                    'default_value' => 10
                ]
            ]
        ];

        $this->assertEquals(
            $handlerConfig,
            $this->map->getHandlerConfigs('source-document', 'field-with-handler', MapInterface::TYPE_SOURCE)
        );

        $this->assertEquals([], $this->map->getHandlerConfigs(
            'source-document',
            'some-field',
            MapInterface::TYPE_SOURCE
        ));
    }

    /**
     * @return void
     */
    public function testIsDocumentIgnoredSource()
    {
        $this->assertTrue($this->map->isDocumentIgnored('source-document-ignored', MapInterface::TYPE_SOURCE));
        $this->assertTrue($this->map->isDocumentIgnored('source-document-ignored-wc', MapInterface::TYPE_SOURCE));
        // Second run to check cached value
        $this->assertTrue($this->map->isDocumentIgnored('source-document-ignored', MapInterface::TYPE_SOURCE));
    }

    /**
     * @return void
     */
    public function testIsDocumentIgnoredDest()
    {
        $this->assertTrue($this->map->isDocumentIgnored('dest-document-ignored', MapInterface::TYPE_DEST));
        $this->assertTrue($this->map->isDocumentIgnored('dest-document-ignored1', MapInterface::TYPE_DEST));
        // Second run to check cached value
        $this->assertTrue($this->map->isDocumentIgnored('dest-document-ignored', MapInterface::TYPE_DEST));
    }

    /**
     * @return void
     */
    public function testIsFieldDataTypeIgnored()
    {
        $this->assertTrue($this->map->isFieldDataTypeIgnored('dest-document5', 'field6', MapInterface::TYPE_SOURCE));
        $this->assertTrue($this->map->isFieldDataTypeIgnored('dest-document5', 'field6', MapInterface::TYPE_DEST));
    }
}
