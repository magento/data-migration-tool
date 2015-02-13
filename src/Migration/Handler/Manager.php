<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Magento\Framework\ObjectManagerInterface;
use Migration\MapReader;
use Migration\Resource\Document;
use Migration\Resource\Record;
use Migration\Resource\Record\Collection;
use Migration\Resource\Structure;

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
        $handlerName = $handlerConfig['class'];
        if (empty($handlerName)) {
            return;
        }
        $handler = $this->objectManager->create($handlerName, $handlerConfig['class']);
        if (!$handler instanceof HandlerInterface) {
            throw new \Exception("'$handlerName' is not correct handler.");
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
        if ($this->handlers[$field]) {
            return $this->handlers[$field];
        }
        return null;
    }
}
