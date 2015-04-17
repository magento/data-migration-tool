<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App;

/**
 * Class ShellTest
 */
class SetupChangeLogTest extends \PHPUnit_Framework_TestCase
{

    public function testSetupChangeLog()
    {
        /** @var \Migration\Resource\Source|\PHPUnit_Framework_MockObject_MockObject $source */
        $source = $this->getMock('\Migration\Resource\Source', [], [], '', false);
        $source->expects($this->exactly(2))
            ->method('createDelta')
            ->withConsecutive(
                ['orders', 'order_id'],
                ['invoices', 'invoice_id']
            );

        /** @var \Migration\MapReader\MapReaderChangelog|\PHPUnit_Framework_MockObject_MockObject $mapReader */
        $mapReader = $this->getMock('\Migration\MapReader\MapReaderChangelog', [], [], '', false);
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

        $changelog = new SetupChangeLog($source, $mapReader, $progress);
        $this->assertTrue($changelog->perform());
    }
}
