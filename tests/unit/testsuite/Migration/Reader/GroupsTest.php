<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

/**
 * Class GroupTest
 */
class GroupsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Groups
     */
    protected $groups;

    /**
     * @var \Magento\Framework\App\Arguments\ValidationState|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validationState;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->validationState = $this->getMockBuilder(\Magento\Framework\App\Arguments\ValidationState::class)
            ->disableOriginalConstructor()
            ->setMethods(['isValidationRequired'])
            ->getMock();
        $this->validationState->expects($this->any())->method('isValidationRequired')->willReturn(true);
    }

    /**
     * @return void
     */
    public function testGetGroupAttributes()
    {
        $groupsFile = 'tests/unit/testsuite/Migration/_files/eav-attribute-groups.xml';
        $attributes = [
            'url_key' => ['catalog_product', 'catalog_category'],
            'msrp_enabled' => ['catalog_product']
        ];
        $groups = new Groups($this->validationState, $groupsFile);
        $this->assertEquals($attributes, $groups->getGroup('ignore'));
    }

    /**
     * @return void
     */
    public function testGetGroupDocuments()
    {
        $groupsFile = 'tests/unit/testsuite/Migration/_files/eav-document-groups.xml';
        $documents = [
            'catalog_eav_attribute' => 'attribute_id',
            'customer_eav_attribute' => 'attribute_id',
            'eav_entity_type' => 'entity_type_id'
        ];
        $groups = new Groups($this->validationState, $groupsFile);
        $this->assertEquals($documents, $groups->getGroup('mapped_documents'));
    }

    /**
     * @return void
     */
    public function testGetGroups()
    {
        $groupsFile = 'tests/unit/testsuite/Migration/_files/eav-document-groups.xml';
        $documents = [
            'documents' => [
                'eav_attribute_group' => '', 'eav_attribute_set' => ''
            ],
            'mapped_documents' => [
                'catalog_eav_attribute' => 'attribute_id',
                'customer_eav_attribute' => 'attribute_id',
                'eav_entity_type' => 'entity_type_id'
            ],
            'documents_leftover_values' => [
                'catalog_category_entity_datetime' => ''
            ],
        ];
        $groups = new Groups($this->validationState, $groupsFile);
        $this->assertEquals($documents, $groups->getGroups());
    }
}
