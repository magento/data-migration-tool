<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App;

/**
 * Class ShellTest
 */
class SetupDeltaLogTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @return void
     */
    public function testPerform()
    {
        /** @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject $source */
        $source = $this->createMock(\Migration\ResourceModel\Source::class);
        /** @var \Migration\ResourceModel\Document|\PHPUnit_Framework_MockObject_MockObject $source */
        $document = $this->createMock(\Migration\ResourceModel\Document::class);
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
        $readerGroups = $this->createMock(\Migration\Reader\Groups::class);
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
        $groupsFactory = $this->createMock(\Migration\Reader\GroupsFactory::class);
        $groupsFactory->expects($this->any())->method('create')->with('delta_document_groups_file')
            ->willReturn($readerGroups);

        /** @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject $progress */
        $progress = $this->createMock(\Migration\App\ProgressBar\LogLevelProcessor::class);
        $progress->expects($this->once())
            ->method('start')
            ->with(4);
        $progress->expects($this->exactly(4))
            ->method('advance');
        $progress->expects($this->once())
            ->method('finish');

        /** @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock(\Migration\Logger\Logger::class);

        $deltaLog = new SetupDeltaLog($source, $groupsFactory, $progress, $logger);
        $this->assertTrue($deltaLog->perform());
    }
}
