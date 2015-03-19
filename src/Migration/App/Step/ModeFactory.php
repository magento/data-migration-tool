<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

use Magento\Framework\ObjectManagerInterface;
use Migration\Exception;

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
     * @throws Exception
     */
    public function create($mode)
    {
        $mode = $this->objectManager->create(__NAMESPACE__ . '\\Mode\\' . ucfirst($mode));
        if (!($mode instanceof ModeInterface)) {
            throw new Exception("Mode class must implement ModeInterface.");
        }
        return $mode;
    }
}
