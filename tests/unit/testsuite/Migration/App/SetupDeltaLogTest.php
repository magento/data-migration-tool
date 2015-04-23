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

    public function testPerform()
    {
        /** @var \Migration\Resource\Source|\PHPUnit_Framework_MockObject_MockObject $source */
        $source = $this->getMock('\Migration\Resource\Source', [], [], '', false);
        $source->expects($this->exactly(2))
            ->method('createDelta')
            ->withConsecutive(
                ['orders', 'order_id'],
                ['invoices', 'invoice_id']
            );

        /** @var \Migration\Reader\Map|\PHPUnit_Framework_MockObject_MockObject $readerMap */
        $readerMap = $this->getMock('\Migration\Reader\Map', [], [], '', false);
        $readerMap->expects($this->any())
            ->method('getDeltaDocuments')
            ->with()
            ->willReturn(
                ['orders' => 'order_id', 'invoices' => 'invoice_id']
            );

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('deltalog_map_file')->willReturn($readerMap);

        /** @var \Migration\App\ProgressBar|\PHPUnit_Framework_MockObject_MockObject $progress */
        $progress = $this->getMock('\Migration\App\ProgressBar', [], [], '', false);
        $progress->expects($this->once())
            ->method('start')
            ->with(2);
        $progress->expects($this->exactly(2))
            ->method('advance');
        $progress->expects($this->once())
            ->method('finish');

        $deltalog = new SetupDeltaLog($source, $mapFactory, $progress);
        $this->assertTrue($deltalog->perform());
    }
}
