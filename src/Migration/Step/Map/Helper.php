<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Reader\GroupsFactory;

class Helper
{
    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerGroups;

    /**
     * @var []
     */
    protected $documentsDuplicateOnUpdate = [];

    /**
     * @param GroupsFactory $groupsFactory
     */
    public function __construct(
        GroupsFactory $groupsFactory
    ) {
        $this->readerGroups = $groupsFactory->create('map_document_groups');
        $this->documentsDuplicateOnUpdate = $this->readerGroups->getGroup('destination_documents_update_on_duplicate');
    }

    /**
     * @param string $documentName
     * @return array|bool
     */
    public function getFieldsUpdateOnDuplicate($documentName)
    {
        $updateOnDuplicate = false;
        if (array_key_exists($documentName, $this->documentsDuplicateOnUpdate)) {
            $updateOnDuplicate = explode(',', $this->documentsDuplicateOnUpdate[$documentName]);
        }
        return $updateOnDuplicate;
    }
}
