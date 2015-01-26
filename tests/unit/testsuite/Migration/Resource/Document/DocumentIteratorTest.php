<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

class DocumentIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Resource\Document\DocumentFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentFactory;

    /**
     * @var \Migration\Resource\Record\RecordIteratorInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordIterator;

    /**
     * @var \Migration\Resource\Document\Document | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $document;

    /**
     * @var \Migration\Resource\Document\ProviderInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentProvider;

    /**
     * @var \Migration\Resource\Document\DocumentIterator
     */
    protected $documentIterator;

    protected function setUp()
    {
        $this->documentFactory = $this->getMock(
            '\Migration\Resource\Document\DocumentFactory',
            array(),
            array(),
            '',
            false
        );
        $this->recordIterator = $this->getMockForAbstractClass(
            '\Migration\Resource\Record\RecordIteratorInterface',
            array(),
            '',
            false
        );
        $this->document = $this->getMock(
            '\Migration\Resource\Document\Document',
            array(),
            array(),
            '',
            false
        );
        $this->documentProvider = $this->getMockForAbstractClass(
            '\Migration\Resource\Document\ProviderInterface',
            array(),
            '',
            false
        );
        $this->documentProvider->expects($this->any())
            ->method('getDocumentList')
            ->will($this->returnValue(['doc1', 'doc2', 'doc3', 'doc4', 'doc5']));

        $this->documentIterator = new \Migration\Resource\Document\DocumentIterator(
            $this->documentFactory,
            $this->recordIterator
        );

        $this->documentIterator->setDocumentProvider($this->documentProvider);
    }

    public function testCurrent()
    {
        $this->documentFactory->expects($this->any())
            ->method('create')
            ->with($this->equalTo(array(
                'documentProvider' => $this->documentProvider,
                'recordIterator' => $this->recordIterator,
                'documentName' => 'doc1'
            )))
            ->will($this->returnValue($this->document));
        $this->assertSame($this->document, $this->documentIterator->current());
    }

    public function testKey()
    {
        $this->documentIterator->seek(2);
        $this->assertEquals(2, $this->documentIterator->key());
    }

    public function testNext()
    {
        $this->assertEquals(0, $this->documentIterator->key());
        $this->documentIterator->next();
        $this->assertEquals(1, $this->documentIterator->key());
    }

    public function testRewind()
    {
        $this->documentIterator->seek(2);
        $this->assertEquals(2, $this->documentIterator->key());
        $this->documentIterator->rewind();
        $this->assertEquals(0, $this->documentIterator->key());
    }

    public function testSeek()
    {
        $this->assertEquals(0, $this->documentIterator->key());
        $this->documentIterator->seek(3);
        $this->assertEquals(3, $this->documentIterator->key());
    }

    public function testValid()
    {
        $this->documentIterator->seek(3);
        $this->assertTrue($this->documentIterator->valid());
    }

    public function testNotValid()
    {
        $this->documentIterator->seek(10);
        $this->assertFalse($this->documentIterator->valid());
    }

    public function testCount()
    {
        $this->assertEquals(5, $this->documentIterator->count());
    }

    public function testSetDocumentProvider()
    {
        $provider = $this->getMockForAbstractClass(
            '\Migration\Resource\Document\ProviderInterface',
            array(),
            '',
            false
        );
        $provider->expects($this->atLeastOnce())
            ->method('getDocumentList')
            ->will($this->returnValue(['doc1', 'doc2', 'doc3', 'doc4', 'doc5']));
        $this->documentIterator->setDocumentProvider($provider);
    }

    public function testIterator()
    {
        $result = '';
        $this->documentFactory->expects($this->any())
            ->method('create')
            ->willReturnCallback(function($data){
                $this->assertSame($this->documentProvider, $data['documentProvider']);
                $this->assertSame($this->recordIterator, $data['recordIterator']);
                $document = $this->getMock('\Migration\Resource\Document\Document', array(), array(), '', false);
                $document->expects($this->any())
                    ->method('getName')
                    ->will($this->returnValue($data['documentName']));
                return $document;
            });

        foreach($this->documentIterator as $key => $value) {
            $result .= ' ' . $key . '=>' . $value->getName();
        }

        $this->assertEquals(' 0=>doc1 1=>doc2 2=>doc3 3=>doc4 4=>doc5', $result);
    }
}
