<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration;

use Migration\Reader\MapInterface;

/**
 * Class RecordTransformerTest
 */
class RecordTransformerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResourceModel\Document|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sourceDocument;

    /**
     * @var ResourceModel\Document|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destDocument;

    /**
     * @var MapInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapReader;

    /**
     * @var Handler\ManagerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $handlerManagerFactory;

    /**
     * @var RecordTransformer
     */
    protected $recordTransformer;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->sourceDocument = $this->createPartialMock(
            \Migration\ResourceModel\Document::class,
            ['getStructure', 'getName']
        );
        $this->destDocument = $this->createPartialMock(
            \Migration\ResourceModel\Document::class,
            ['getStructure']
        );
        $this->mapReader = $this->getMockForAbstractClass(
            \Migration\Reader\MapInterface::class,
            [],
            '',
            false
        );
        $this->mapReader->expects($this->any())->method('getHandlerConfigs')->willReturn([
            ['class' => 'FirstHandlerFullyQualifiedName', 'params' => []],
            ['class' => 'SecondHandlerFullyQualifiedName', 'params' => []]
        ]);
        $this->handlerManagerFactory = $this->getMockBuilder(\Migration\Handler\ManagerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->recordTransformer = new RecordTransformer(
            $this->sourceDocument,
            $this->destDocument,
            $this->handlerManagerFactory,
            $this->mapReader
        );
    }

    /**
     * @param ResourceModel\Document|\PHPUnit_Framework_MockObject_MockObject $document
     * @param int $callNumber
     * @return \Migration\Handler\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function initHandler($document, $callNumber = 1)
    {
        $handlerManager = $this->createPartialMock(
            \Migration\Handler\Manager::class,
            ['initHandler', 'getHandlers']
        );
        $this->handlerManagerFactory->expects($this->at($callNumber))->method('create')->will(
            $this->returnValue($handlerManager)
        );
        $structure = $this->createPartialMock(
            \Migration\ResourceModel\Structure::class,
            ['getFields']
        );
        $document->expects($this->once())->method('getStructure')->will($this->returnValue($structure));
        $fields = ['field1' => '', 'field2' => '', 'field3' => '',];
        $structure->expects($this->once())->method('getFields')->will($this->returnValue($fields));
        $handlerManager->expects($this->any())->method('initHandler');
        return $handlerManager;
    }

    /**
     * @return void
     */
    public function testInit()
    {
        $this->initHandler($this->sourceDocument, 0);
        $this->initHandler($this->destDocument, 1);
        $this->recordTransformer->init();
    }

    /**
     * @return void
     */
    public function testTransform()
    {
        $srcHandler = $this->initHandler($this->sourceDocument, 0);
        $destHandler = $this->initHandler($this->destDocument, 1);
        $this->recordTransformer->init();
        $this->sourceDocument->expects($this->any())->method('getName')->willReturn('source_document_name');
        $recordFrom = $this->createMock(\Migration\ResourceModel\Record::class);
        $recordFrom->expects($this->any())->method('getFields')->will($this->returnValue(['field1', 'field2']));
        $recordFrom->expects($this->any())->method('getData')->will($this->returnValue(['field1' => 1, 'field2' => 2]));
        $recordTo = $this->createMock(\Migration\ResourceModel\Record::class);
        $recordTo->expects($this->any())->method('getFields')->will($this->returnValue(['field2']));
        $recordTo->expects($this->any())->method('setData')->with(['field2' => 2]);

        $field2Handler = $this->createPartialMock(
            \Migration\Handler\SetValue::class,
            ['handle']
        );
        $field2Handler->expects($this->once())->method('handle');
        $srcHandler->expects($this->any())->method('getHandlers')->willReturn(['field2' => $field2Handler]);
        $destHandler->expects($this->any())->method('getHandlers')->willReturn([]);
        $this->mapReader->expects($this->any())->method('getFieldMap')->with('source_document_name', 'field1')
            ->willReturnArgument(1);
        $this->recordTransformer->transform($recordFrom, $recordTo);
    }
}
