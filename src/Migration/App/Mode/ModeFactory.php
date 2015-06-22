<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Mode;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ModeFactory
 */
class ModeFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $mode
     * @return ModeInterface
     * @throws \Migration\Exception
     */
    public function create($mode)
    {
        if (!class_exists('\\Migration\\Mode\\' . ucfirst($mode))) {
            throw new \Migration\Exception(sprintf("Mode '%s' does not exist.", $mode));
        }
        $mode = $this->objectManager->create('\\Migration\\Mode\\' . ucfirst($mode), ['mode' => $mode]);
        if (!($mode instanceof \Migration\App\Mode\ModeInterface)) {
            throw new \Migration\Exception('Mode class must implement ModeInterface.');
        }
        return $mode;
    }
}
