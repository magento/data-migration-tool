<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Rule;

use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Destination;

/**
 * Class NormalizationIdsTest
 */
class NormalizationIdsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConditionSql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $handler;

    /**
     * @var Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var string
     */
    protected $normalizationDocument = 'catalogrule_website';

    /**
     * @var string
     */
    protected $normalizationField = 'website_id';

    /**
     * @return void
     */
    public function setUp()
    {
        $this->destination = $this->getMockBuilder('Migration\ResourceModel\Destination')->disableOriginalConstructor()
            ->setMethods(['clearDocument', 'saveRecords'])
            ->getMock();
        $this->handler = new NormalizationIds(
            $this->destination,
            $this->normalizationDocument,
            $this->normalizationField
        );
    }

    /**
     * @return void
     */
    public function testHandle()
    {
        $fieldNameRuleId = 'rule_id';
        $fieldNameForNormalization = 'website_ids';
        $idsForNormalization = '1,2,3';
        $normalizedData = [
            [$fieldNameRuleId => 1, $this->normalizationField => 1],
            [$fieldNameRuleId => 1, $this->normalizationField => 2],
            [$fieldNameRuleId => 1, $this->normalizationField => 3],
        ];

        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->setMethods(['getValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Record $oppositeRecord|\PHPUnit_Framework_MockObject_MockObject */
        $oppositeRecord = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->disableOriginalConstructor()
            ->getMock();

        $recordToHandle->expects($this->once())
            ->method('getFields')
            ->willReturn([$fieldNameRuleId, $fieldNameForNormalization]);
        $recordToHandle->expects($this->any())->method('getValue')->willReturnMap([
            [$fieldNameRuleId, 1],
            [$fieldNameForNormalization, $idsForNormalization],
        ]);
        $this->destination
            ->expects($this->once())
            ->method('clearDocument')
            ->with($this->normalizationDocument)
            ->willReturnSelf();
        $this->destination
            ->expects($this->once())
            ->method('saveRecords')
            ->with($this->normalizationDocument, $normalizedData)
            ->willReturnSelf();
        $this->handler->setField($fieldNameForNormalization);
        $this->handler->handle($recordToHandle, $oppositeRecord);
    }
}
