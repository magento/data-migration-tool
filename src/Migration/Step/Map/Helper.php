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
        foreach ($this->documentsDuplicateOnUpdate as $document => $fields) {
            $this->documentsDuplicateOnUpdate[$document] = explode(',', $fields);
        }
    }

    /**
     * @param string $documentName
     * @return array|bool
     */
    public function getFieldsUpdateOnDuplicate($documentName)
    {
        return (!empty($this->documentsDuplicateOnUpdate[$documentName]))
            ? $this->documentsDuplicateOnUpdate[$documentName]
            : false;
    }

    /**
     *  Get all documents for duplicate on update operation
     *
     * @return array
     */
    public function getDocumentsDuplicateOnUpdate()
    {
        return $this->documentsDuplicateOnUpdate;
    }
}
