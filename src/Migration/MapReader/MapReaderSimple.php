<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\MapReader;

use Migration\Config;
use Migration\Exception;

/**
 * Class MapReaderSimple
 */
class MapReaderSimple
{
    const CONFIGURATION_SCHEMA = 'map_simple.xsd';

    /**
     * @var \DOMXPath
     */
    protected $xml;

    /**
     * @param Config $config
     * @param string $optionName
     * @throws Exception
     */
    public function __construct(Config $config, $optionName = '')
    {
        $this->config = $config;
        if (!empty($optionName)) {
            $this->init($this->config->getOption($optionName));
        }
    }

    /**
     * Init configuration
     *
     * @param string $mapFile
     * @return $this
     * @throws Exception
     */
    public function init($mapFile)
    {
        $this->ignoredDocuments = [];
        $this->wildcards = null;

        $configFile = $this->getRootDir() . $mapFile;
        if (!is_file($configFile)) {
            throw new Exception('Invalid map filename: ' . $configFile);
        }

        $xml = file_get_contents($configFile);
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
    public function getList($name)
    {
        $result = [];
        if (!$this->xml) {
            return $result;
        }
        $queryResult = $this->xml->query(sprintf('//list[@name="%s"]', $name));
        if ($queryResult->length > 0) {
            /** @var \DOMElement $document */
            $node = $queryResult->item(0);
            /** @var \DOMElement $item */
            foreach ($node->childNodes as $item) {
                if ($item->nodeType == XML_ELEMENT_NODE) {
                    if ($item->getAttribute('key') !== '') {
                        $result[$item->getAttribute('key')] = $item->nodeValue;
                    } else {
                        $result[] = $item->nodeValue;
                    }
                }
            }
        }
        return $result;
    }
}
