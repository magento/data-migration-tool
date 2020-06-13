<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\Reader\GroupsFactory;
use Migration\ResourceModel\Destination;
use Migration\Step\Eav\Model\IgnoredAttributes;
use Migration\Step\Eav\Model\Data as ModelData;

/**
 * Class Volume
 */
class Volume extends AbstractVolume
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var InitialData
     */
    protected $initialData;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $groups;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var array
     */
    protected $tableKeys;

    /**
     * @var IgnoredAttributes
     */
    protected $ignoredAttributes;

    /**
     * @param Helper $helper
     * @param InitialData $initialData
     * @param IgnoredAttributes $ignoredAttributes
     * @param Logger $logger
     * @param ProgressBar\LogLevelProcessor $progress
     * @param GroupsFactory $groupsFactory
     * @param Destination $destination
     */
    public function __construct(
        Helper $helper,
        InitialData $initialData,
        IgnoredAttributes $ignoredAttributes,
        Logger $logger,
        ProgressBar\LogLevelProcessor $progress,
        GroupsFactory $groupsFactory,
        Destination $destination
    ) {
        $this->initialData = $initialData;
        $this->ignoredAttributes = $ignoredAttributes;
        $this->helper = $helper;
        $this->progress = $progress;
        $this->groups = $groupsFactory->create('eav_document_groups_file');
        $this->destination = $destination;
        parent::__construct($logger);
    }

    /**
     * Perform
     *
     * @return bool
     */
    public function perform()
    {
        $this->progress->start(2);
        $this->checkAttributesMismatch();
        $this->progress->finish();
        $result = $this->checkForErrors(Logger::ERROR);
        if ($result) {
            $this->helper->deleteBackups();
        }
        return $result;
    }

    /**
     * Check attributes mismatch
     *
     * @return void
     */
    private function checkAttributesMismatch()
    {
        foreach ($this->helper->getDestinationRecords('eav_attribute') as $attribute) {
            $sourceAttributes = $this->ignoredAttributes
                ->clearIgnoredAttributes($this->initialData->getAttributes(ModelData::TYPE_SOURCE));

            if (isset($sourceAttributes[$attribute['attribute_id']])
                && ($sourceAttributes[$attribute['attribute_id']]['attribute_code'] != $attribute['attribute_code'])
            ) {
                $this->errors[] = sprintf(
                    'Source and Destination attributes mismatch. Attribute id:%s',
                    $attribute['attribute_id']
                );
            }
        }
        $this->progress->advance();
    }
}
