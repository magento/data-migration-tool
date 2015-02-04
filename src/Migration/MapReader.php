<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration;

/**
 * Class MapReader
 */
class MapReader
{
    const CONFIGURATION_FILE = 'map.xml';
    const CONFIGURATION_SCHEMA = 'map.xsd';

    const TYPE_SOURCE = 'source';
    const TYPE_DEST = 'destination';

    /**
     * @var \DOMXPath
     */
    protected $xml;

    /**
     * Init configuration
     *
     * @param string $configFile
     * @return $this
     * @throws \Exception
     */
    public function init($configFile = null)
    {
        if (is_null($configFile)) {
            $configFile = $this->getConfigDirectoryPath() . self::CONFIGURATION_FILE;
        }

        if (empty($configFile) || !file_exists($configFile)) {
            throw new \Exception('Invalid map filename: ' . $configFile);
        }

        $xml = file_get_contents($configFile);
        $document = new \Magento\Framework\Config\Dom($xml);

        if (!$document->validate($this->getConfigDirectoryPath() . self::CONFIGURATION_SCHEMA)) {
            throw new \Exception('XML file is invalid.');
        }

        $this->xml = new \DOMXPath($document->getDom());
        return $this;
    }

    /**
     * Get Migration Tool Configuration Dir
     * @return string
     */
    protected function getConfigDirectoryPath()
    {
        return dirname(dirname(__DIR__)) . '/etc/';
    }

    /**
     * @param string $document
     * @param string $type
     * @return bool
     */
    public function hasDocument($document, $type = self::TYPE_SOURCE)
    {
        /** @var \DOMNodeList $result */
        $result = $this->xml->query(sprintf('//%s/document_rules/*/document[text()="%s"]', $type, $document));
        return $result->length > 0;
    }

    /**
     * @param string $document
     * @param string $field
     * @param string $type
     * @return bool
     */
    public function hasField($document, $field, $type = self::TYPE_SOURCE)
    {
        /** @var \DOMNodeList $result */
        $result = $this->xml->query(sprintf('//%s/field_rules/*/field[text()="%s::%s"]', $type, $document, $field));
        return $result->length > 0;
    }

    /**
     * @param string $document
     * @param string $type
     * @return array
     */
    public function getDocumentMap($document, $type = self::TYPE_SOURCE)
    {
        $map = [];
        if ($this->hasDocument($document, $type)) {
            $nodes = $this->xml->query(sprintf('//%s/document_rules/*/document[text()="%s"]', $type, $document));
            /** @var \DOMElement $item */
            $item = $nodes->item(0);

            if ($item->parentNode->nodeName == 'ignore') {
                $map[$document] = '@ignored';
            }
            if ($item->parentNode->nodeName == 'rename') {
                $to = $item->parentNode->getElementsByTagName('to');
                if ($to->length) {
                    $map[$document] = $to->item(0)->nodeValue;
                }
            }
        }
        return $map;
    }

    /**
     * @param string $document
     * @param string $field
     * @param string $type
     * @return array
     */
    public function getFieldMap($document, $field, $type = self::TYPE_SOURCE)
    {
        $map = [];
        if ($this->hasField($document, $field, $type)) {
            $nodes = $this->xml->query(sprintf('//%s/field_rules/*/field[text()="%s::%s"]', $type, $document, $field));
            /** @var \DOMElement $item */
            $item = $nodes->item(0);
            $key = "$document::$field";

            if ($item->parentNode->nodeName == 'ignore') {
                $map[$key] = '@ignored';
            }

            if ($item->parentNode->nodeName == 'move') {
                $to = $item->parentNode->getElementsByTagName('to');
                if ($to->length) {
                    $map[$key] = $to->item(0)->nodeValue;
                }
            }
        }
        return $map;
    }

    /**
     * @param string $document
     * @param string $field
     * @param string $type
     * @return array
     */
    public function getHandlerConfig($document, $field, $type = self::TYPE_SOURCE)
    {
        $config = [];
        if ($this->hasField($document, $field, $type)) {
            $nodes = $this->xml->query(
                sprintf('//%s/field_rules/transform[field/text()="%s::%s"]/handler', $type, $document, $field)
            );
            /** @var \DOMElement $node */
            foreach ($nodes as $node) {
                $config['class'] = $node->getAttribute('class');
                /** @var \DOMElement $param */
                foreach ($node->childNodes as $param) {
                    if ($param->nodeType == XML_ELEMENT_NODE) {
                        $config['params'][$param->getAttribute('name')] = $param->getAttribute('value');
                    }
                }
            }
        }
        return $config;
    }
}
