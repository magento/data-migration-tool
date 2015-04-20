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

        /** @var \Migration\MapReader\MapReaderDeltalog|\PHPUnit_Framework_MockObject_MockObject $mapReader */
        $mapReader = $this->getMock('\Migration\MapReader\MapReaderDeltalog', [], [], '', false);
        $mapReader->expects($this->any())
            ->method('getDeltaDocuments')
            ->with()
            ->willReturn(
                ['orders' => 'order_id', 'invoices' => 'invoice_id']
            );

        /** @var \Migration\ProgressBar|\PHPUnit_Framework_MockObject_MockObject $progress */
        $progress = $this->getMock('\Migration\ProgressBar', [], [], '', false);
        $progress->expects($this->once())
            ->method('start')
            ->with(2);
        $progress->expects($this->exactly(2))
            ->method('advance');
        $progress->expects($this->once())
            ->method('finish');

        $deltalog = new SetupDeltaLog($source, $mapReader, $progress);
        $this->assertTrue($deltalog->perform());
    }
}
