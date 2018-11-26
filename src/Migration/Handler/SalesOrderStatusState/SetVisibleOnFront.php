<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\SalesOrderStatusState;

use Migration\ResourceModel\Record;
use Migration\Handler\AbstractHandler;
use Migration\Handler\HandlerInterface;

/**
 * Handler to set constant value to the field
 */
class SetVisibleOnFront extends AbstractHandler implements HandlerInterface
{
    /**
     * @var array
     */
    protected $initiallyInvisible = [
        'pending_ogone-pending_payment' => 0,
        'pending_payment-pending_payment' => 0,
        'processed_ogone-processing' => 0
    ];

    /**
     * @var array
     */
    protected $invisibleStates = ['pending_payment'];

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $key = $recordToHandle->getValue('status') . '-' . $recordToHandle->getValue('state');
        if (isset($this->initiallyInvisible[$key])
            || in_array($recordToHandle->getValue('state'), $this->invisibleStates)
        ) {
            $recordToHandle->setValue('visible_on_front', 0);
        } else {
            $recordToHandle->setValue('visible_on_front', 1);
        }
    }
}
