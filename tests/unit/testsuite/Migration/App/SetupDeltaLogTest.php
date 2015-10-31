<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App;

/**
 * Class ShellTest
 */
class SetupDeltaLogTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return void
     */
    public function testPerform()
    {
        /** @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject $source */
        $source = $this->getMock('\Migration\ResourceModel\Source', [], [], '', false);
        /** @var \Migration\ResourceModel\Document|\PHPUnit_Framework_MockObject_MockObject $source */
        $document = $this->getMock('\Migration\ResourceModel\Document', [], [], '', false);
        $source->expects($this->any())
            ->method('getDocument')
            ->willReturn($document);
        $source->expects($this->exactly(4))
            ->method('createDelta')
            ->withConsecutive(
                ['orders', 'order_id'],
                ['invoices', 'invoice_id'],
                ['reports', 'report_id'],
                ['shipments', 'shipment_id']
            );

        /** @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject $readerGroups */
        $readerGroups = $this->getMock('\Migration\Reader\Groups', [], [], '', false);
        $readerGroups->expects($this->any())
            ->method('getGroups')
            ->with()
            ->willReturn(
                [
                    'firstGroup' => ['orders' => 'order_id', 'invoices' => 'invoice_id'],
                    'secondGroup' => ['reports' => 'report_id', 'shipments' => 'shipment_id']
                ]
            );

        /** @var \Migration\Reader\GroupsFactory|\PHPUnit_Framework_MockObject_MockObject $groupsFactory */
        $groupsFactory = $this->getMock('\Migration\Reader\GroupsFactory', [], [], '', false);
        $groupsFactory->expects($this->any())->method('create')->with('delta_document_groups_file')
            ->willReturn($readerGroups);

        /** @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject $progress */
        $progress = $this->getMock('\Migration\App\ProgressBar\LogLevelProcessor', [], [], '', false);
        $progress->expects($this->once())
            ->method('start')
            ->with(4);
        $progress->expects($this->exactly(4))
            ->method('advance');
        $progress->expects($this->once())
            ->method('finish');

        $deltaLog = new SetupDeltaLog($source, $groupsFactory, $progress);
        $this->assertTrue($deltaLog->perform());
    }
}
