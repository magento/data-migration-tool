<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Handler;
use Migration\Reader\Map;
use Migration\ResourceModel;

/**
 * Class HelperTest
 */
class HelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groups;

    /**
     * @var array
     */
    protected $fieldsUpdateOnDuplicate = ['document' => 'document1', 'fields' => ['field1', 'field2']];

    /**
     * @return void
     */
    public function setUp()
    {
        $this->groups = $this->getMockBuilder(\Migration\Reader\Groups::class)
            ->disableOriginalConstructor()
            ->setMethods(['getGroup'])
            ->getMock();
        $this->groups
            ->expects($this->once())
            ->method('getGroup')
            ->with('destination_documents_update_on_duplicate')
            ->willReturn([
                $this->fieldsUpdateOnDuplicate['document'] => implode(',', $this->fieldsUpdateOnDuplicate['fields'])
            ]);
        /** @var \Migration\Reader\GroupsFactory|\PHPUnit_Framework_MockObject_MockObject $groupsFactory */
        $groupsFactory = $this->getMockBuilder(\Migration\Reader\GroupsFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $groupsFactory->expects($this->any())
            ->method('create')
            ->with('map_document_groups')
            ->willReturn($this->groups);
        $this->helper = new Helper($groupsFactory);
    }

    /**
     * @return void
     */
    public function testGetFieldsUpdateOnDuplicate()
    {
        $this->assertEquals(
            $this->fieldsUpdateOnDuplicate['fields'],
            $this->helper->getFieldsUpdateOnDuplicate($this->fieldsUpdateOnDuplicate['document'])
        );
    }
}
