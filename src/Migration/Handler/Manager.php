<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Magento\Framework\ObjectManagerInterface;
use Migration\Exception;

/**
 * Class Manager
 */
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
     * Init handler
     *
     * @param string $field
     * @param array $handlerConfig
     * @param string|null $handlerKey
     * @return void
     * @throws Exception
     */
    public function initHandler($field, $handlerConfig = [], $handlerKey = null)
    {
        if (empty($handlerConfig)) {
            return;
        }

        if (empty($handlerConfig['class'])) {
            throw new Exception('Handler class name not specified.');
        }

        $handler = $this->objectManager->create($handlerConfig['class'], $handlerConfig['params']);
        if (!($handler instanceof HandlerInterface)) {
            throw new Exception("'{$handlerConfig['class']}' is not correct handler.");
        }
        $handler->setField($field);
        $handlerKey = $handlerKey ?: $field;
        $this->handlers[$handlerKey] = $handler;
    }

    /**
     * Get handler for $field field
     *
     * @param string $handlerKey
     * @return HandlerInterface|null
     */
    public function getHandler($handlerKey)
    {
        if (!empty($this->handlers[$handlerKey])) {
            return $this->handlers[$handlerKey];
        }
        return null;
    }

    /**
     * Get all handlers
     *
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }
}
