<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;

/**
 * Class SetDefaultWebsiteId
 */
class SetDefaultWebsiteId extends AbstractHandler
{

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var string
     */
    protected $defaultWebsiteId;

    /**
     * @param Source $source
     */
    public function __construct(Source $source)
    {
        $this->source = $source;
        $this->defaultWebsiteId = null;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        if (empty($this->defaultWebsiteId)) {
            $this->validate($recordToHandle);
            foreach ($this->source->getRecords('core_website', 0) as $websiteData) {
                if ($websiteData['is_default'] == '1') {
                    $this->defaultWebsiteId = $websiteData[$this->field];
                    break;
                }
            }
        }
        $recordToHandle->setValue($this->field, $this->defaultWebsiteId);
    }
}
