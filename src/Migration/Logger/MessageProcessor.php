<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Logger;

/**
 * Logger messages processor
 */
class MessageProcessor
{
    /**
     * @var array
     */
    protected $extra = [
        'mode' => '',
        'stage' => '',
        'step' => '',
        'table' => ''
    ];

    /**
     * Set extra
     *
     * @param array $record
     * @return array
     */
    public function setExtra(array $record)
    {
        foreach ($record['context'] as $key => $value) {
            switch ($key) {
                case 'mode':
                    $this->extra[$key] = '[mode: ' . $value . ']';
                    break;
                case 'stage':
                    $this->extra[$key] = '[stage: ' . $value . ']';
                    break;
                case 'step':
                    $this->extra[$key] = '[step: ' . $value . ']';
                    break;
                case 'table':
                    $this->extra[$key] = '[table: ' . $value . ']';
                    break;
            }
        }
        $record['extra'] = $this->extra;
        return $record;
    }
}
