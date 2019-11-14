<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Reader\GroupsFactory;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var \Migration\Reader\Groups
     */
    private $readerGroups;

    /**
     * @var []
     */
    private $documentsDuplicateOnUpdate = [];

    /**
     * @var string
     */
    private $groupName = 'delta_map';

    /**
     * @var string
     */
    private $deltaMode = 'delta';

    /**
     * @var []
     */
    private $deltaDocuments;

    /**
     * @param GroupsFactory $groupsFactory
     */
    public function __construct(
        GroupsFactory $groupsFactory
    ) {
        $this->readerGroups = $groupsFactory->create('map_document_groups');
        $this->deltaDocuments = $groupsFactory->create('delta_document_groups_file')->getGroup($this->groupName);
        $this->documentsDuplicateOnUpdate = $this->readerGroups->getGroup('destination_documents_update_on_duplicate');
        foreach ($this->documentsDuplicateOnUpdate as $document => $fields) {
            $this->documentsDuplicateOnUpdate[$document] = explode(',', $fields);
        }
    }

    /**
     * Get fields update on duplicate
     *
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

    /**
     * skip if current mode is delta and the document not within delta list
     *
     * @param $documentName
     * @param $mode
     * @return bool
     */
    public function skipIfDeltaMode($documentName, $mode)
    {
        if ($mode == $this->deltaMode && !in_array($documentName, array_keys($this->deltaDocuments))) {
            return true;
        }
        return false;
    }
}
