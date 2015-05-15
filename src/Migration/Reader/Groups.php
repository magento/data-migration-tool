<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

use Migration\Exception;

/**
 * Class Groups
 */
class Groups
{
    const CONFIGURATION_SCHEMA = 'groups.xsd';

    /**
     * @var \DOMXPath
     */
    protected $xml;

    /**
     * @param string $groupsFile
     * @throws Exception
     */
    public function __construct($groupsFile = '')
    {
        if (!empty($groupsFile)) {
            $this->init($groupsFile);
        }
    }

    /**
     * Init configuration
     *
     * @param string $groupsFile
     * @return $this
     * @throws Exception
     */
    public function init($groupsFile)
    {
        $xmlFile = $this->getRootDir() . $groupsFile;
        if (!is_file($xmlFile)) {
            throw new Exception('Invalid groups filename: ' . $xmlFile);
        }

        $xml = file_get_contents($xmlFile);
        $document = new \Magento\Framework\Config\Dom($xml);

        if (!$document->validate($this->getRootDir() .'etc/' . self::CONFIGURATION_SCHEMA)) {
            throw new Exception('XML file is invalid.');
        }

        $this->xml = new \DOMXPath($document->getDom());
        return $this;
    }

    /**
     * Get Migration Tool Configuration Dir
     * @return string
     */
    protected function getRootDir()
    {
        return dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getGroup($name)
    {
        $result = [];
        if (!$this->xml) {
            return $result;
        }
        $queryResult = $this->xml->query(sprintf('//group[@name="%s"]', $name));
        if ($queryResult->length > 0) {
            /** @var \DOMElement $document */
            $node = $queryResult->item(0);
            /** @var \DOMElement $item */
            foreach ($node->childNodes as $item) {
                if ($item->nodeType == XML_ELEMENT_NODE) {
                    $result[$item->nodeValue] = $item->getAttribute('key');
                }
            }
        }
        return $result;
    }

    /**
     * Get all groups
     *
     * @return array
     */
    public function getGroups()
    {
        $result = [];
        if (!$this->xml) {
            return $result;
        }
        $queryResult = $this->xml->query('//group');
        if ($queryResult->length > 0) {
            /** @var \DOMElement $item */
            foreach ($queryResult as $item) {
                $result[$item->getAttribute('name')] = $this->getGroup($item->getAttribute('name'));
            }
        }
        return $result;
    }
}
