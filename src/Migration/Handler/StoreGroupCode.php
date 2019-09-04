<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\Logger\Logger;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Adapter\Mysql;

/**
 * Handler to create store group code from its name
 */
class StoreGroupCode extends AbstractHandler
{
    /**
     * @var array
     */
    private $storeGroup = [];

    /**
     * @var Source
     */
    private $source;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     * @param Source $source
     */
    public function __construct(Logger $logger, Source $source)
    {
        $this->source = $source;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $code = $this->getCodeFromName($recordToHandle);
        if (empty($code)) {
            $code = 'store_group';
        }
        $recordToHandle->setValue($this->field, $code);
    }

    /**
     * @param Record $recordToHandle
     * @return string
     */
    private function getCodeFromName(Record $recordToHandle)
    {
        $groupIdRecord = $recordToHandle->getValue('group_id');
        $tableName = $recordToHandle->getDocument()->getName();
        if (empty($this->storeGroup)) {
            $names = [];
            /** @var Mysql $adapter */
            $adapter = $this->source->getAdapter();
            $query = $adapter->getSelect()->from(
                $this->source->addDocumentPrefix('core_store_group'),
                ['group_id', 'name']
            );
            $this->storeGroup = $query->getAdapter()->fetchAssoc($query);
            foreach ($this->storeGroup as $groupId => $group) {
                $name = $group['name'];
                $code = preg_replace('/\s+/', '_', $name);
                $code = preg_replace('/[^a-z0-9-_]/', '', strtolower($code));
                $code = preg_replace('/^[^a-z]+/', '', $code);
                if (in_array($name, $names)) {
                    $code = $code . '-' . md5(mt_rand());
                    $this->logger->warning(sprintf(
                        'Duplicated code in %s.%s Record id %s',
                        $tableName,
                        $this->field,
                        $groupId
                    ));
                    $this->logger->warning(PHP_EOL);
                }
                $names[] = $name;
                $this->storeGroup[$groupId]['code'] = $code;
            }
        }
        return $this->storeGroup[$groupIdRecord]['code'];
    }
}
