<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App;

/**
 * Class ProgressTest
 */
class ProgressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Progress\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $file;

    /**
     * @var \Migration\App\Progress
     */
    protected $progress;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->file = $this->getMockBuilder('\Migration\App\Progress\File')
            ->setMethods(['getData', 'saveData', 'clearLockFile'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->progress = new Progress($this->file);
    }

    /**
     * @return array
     */
    public function isCompletedDataProvider()
    {
        $step = $this->getMock('\Migration\Step\Map\Data', [], [], '', false);
        return [
            'complete' => [
                'data' => [get_class($step) => ['integrity' => ['result' => true]]],
                'step' => $step,
                'stage' => 'integrity',
                'result' => true
            ],
            'incomplete' => [
                'data' => [],
                'step' => $step,
                'stage' => 'integrity',
                'result' => false
            ]
        ];
    }

    /**
     * @param array $data
     * @param mixed $step
     * @param string $stage
     * @param bool $result
     * @dataProvider isCompletedDataProvider
     * @return void
     */
    public function testIsCompleted($data, $step, $stage, $result)
    {
        $this->file->expects($this->once())->method('getData')->will($this->returnValue($data));
        $isCompleted = $this->progress->isCompleted($step, $stage);
        $this->assertEquals($result, $isCompleted);
    }

    /**
     * @return void
     */
    public function testAddProcessedEntitySuccess()
    {
        $step = $this->getMock('\Migration\Step\Map\Data', [], [], '', false);
        $stage = 'run';
        $result = $this->progress->addProcessedEntity($step, $stage, 'document_name1');
        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testAddProcessedEntityAlreadyExist()
    {
        $step = $this->getMock('\Migration\Step\Map\Data', [], [], '', false);
        $stage = 'run';
        $documentName = 'document_name1';
        $data = [get_class($step) => [$stage => ['process' => [$documentName]]]];
        $this->file->expects($this->once())->method('getData')->will($this->returnValue($data));
        $result = $this->progress->addProcessedEntity($step, $stage, $documentName);
        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testResetProcessedEntities()
    {
        $step = $this->getMock('\Migration\Step\Map\Migrate', [], [], '', false);
        $stage = 'run';
        $this->progress->resetProcessedEntities($step, $stage);
    }

    /**
     * @return void
     */
    public function testGetProcessedEntities()
    {
        $step = $this->getMock('\Migration\Step\Map\Migrate', [], [], '', false);
        $stage = 'run';
        $document = ['some_document'];
        $progress = [get_class($step) => [$stage => ['process' => $document]]];
        $this->file->expects($this->once())->method('getData')->will($this->returnValue($progress));
        $result = $this->progress->getProcessedEntities($step, $stage);
        $this->assertEquals($document, $result);
    }

    /**
     * @return void
     */
    public function testSaveResult()
    {
        $this->file->expects($this->once())->method('saveData')->will($this->returnValue(1));
        $step = $this->getMock('\Migration\Step\Map', [], [], '', false);
        $this->progress->saveResult($step, 'integrity', 'true');
    }

    /**
     * @return void
     */
    public function testReset()
    {
        $this->file->expects($this->once())->method('clearLockFile');
        $this->progress->reset();
    }

    /**
     * @return void
     */
    public function testResetObject()
    {
        $step = $this->getMock('\Migration\Step\Map', [], [], '', false);
        $data = [get_class($step) => ['dummy_array']];
        $this->file->expects($this->once())->method('getData')->will($this->returnValue($data));
        $this->file->expects($this->once())->method('saveData')->with([]);
        $this->progress->reset($step);
    }

    /**
     * @return void
     */
    public function testSaveDataNoFile()
    {
        $this->file->expects($this->any())->method('isExists')->will($this->returnValue(false));
        $this->file->expects($this->once())->method('saveData');
        $step = $this->getMock('\Migration\Step\Map', [], [], '', false);
        $this->progress->saveResult($step, 'integrity', 'true');

    }

    /**
     * @param array $data
     * @param string $step
     * @param string $stage
     * @param array $processed
     * @param array $saveData
     *
     * @return void
     * @dataProvider saveProcessedEntitiesDataProvider
     */
    public function testSaveProcessedEntities(array $data, $step, $stage, array $processed, array $saveData)
    {
        $this->file->expects($this->once())->method('getData')->willReturn($data);
        $this->file->expects($this->once())->method('saveData')->with($saveData);

        $this->progress->saveProcessedEntities($step, $stage, $processed);
    }

    /**
     * @return array
     */
    public function saveProcessedEntitiesDataProvider()
    {
        $step = $this->getMock('\Migration\Step\Map\Data', [], [], '', false);
        $data = ['test_step' => ['dummy_array']];
        return [
            'class' => [
                'data' => $data,
                'step' => $step,
                'stage' => 'run1',
                'processed' => ['table1'],
                'saveData' => [
                    'test_step' => ['dummy_array'],
                    get_class($step) => ['run1' => [\Migration\App\Progress::PROCESS_KEY => ['table1']]],
                ],
            ],
            'string_name' => [
                'data' => $data,
                'step' => 'step',
                'stage' => 'run2',
                'processed' => ['table2'],
                'saveData' => [
                    'test_step' => ['dummy_array'],
                    'step' => ['run2' => [\Migration\App\Progress::PROCESS_KEY => ['table2']]],
                ],
            ]
        ];
    }
}
