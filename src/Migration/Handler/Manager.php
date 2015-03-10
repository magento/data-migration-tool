<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Magento\Framework\ObjectManagerInterface;

class Manager
{
    /**
     * @var HandlerInterface[]
     */
    protected $handlers = [];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $field
     * @param array $handlerConfig
     * @return void
     * @throws \Exception
     */
    public function initHandler($field, $handlerConfig = [])
    {
        if (empty($handlerConfig)) {
            return;
        }

        if (empty($handlerConfig['class'])) {
            throw new \Exception('Handler class name not specified.');
        }

        $handler = $this->objectManager->create($handlerConfig['class'], $handlerConfig['params']);
        if (!($handler instanceof HandlerInterface)) {
            throw new \Exception("'{$handlerConfig['class']}' is not correct handler.");
        }
        $handler->setField($field);
        $this->handlers[$field] = $handler;
    }

    /**
     * Get handler for $field field
     *
     * @param string $field
     * @return HandlerInterface|null
     */
    public function getHandler($field)
    {
        if (!empty($this->handlers[$field])) {
            return $this->handlers[$field];
        }
        return null;
    }
}
