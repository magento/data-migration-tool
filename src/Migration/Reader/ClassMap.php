<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

use \Migration\Config;
use \Migration\Exception;

/**
 * Class ClassMap
 */
class ClassMap
{
    /**
     * Option in config file, which indicates the location of map file for settings
     */
    const MAP_FILE_OPTION = 'class_map';

    /**
     * XSD schema of configuration file
     */
    const CONFIGURATION_SCHEMA = 'class-map.xsd';

    /**
     * @var \DOMXPath
     */
    protected $xml;

    /**
     * @var array
     */
    protected $map = null;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->validate();
    }

    /**
     * Validating xml file
     *
     * @return $this
     * @throws Exception
     */
    protected function validate()
    {
        $mapFile = $this->config->getOption(self::MAP_FILE_OPTION);
        $rootDir = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR;
        $configFile = $rootDir . $mapFile;
        if (!is_file($configFile)) {
            throw new Exception('Invalid map filename: ' . $configFile);
        }
        $xml = file_get_contents($configFile);
        $document = new \Magento\Framework\Config\Dom($xml);

        if (!$document->validate($rootDir .'etc/' . self::CONFIGURATION_SCHEMA)) {
            throw new Exception('XML file is invalid.');
        }

        $this->xml = new \DOMXPath($document->getDom());
        return $this;
    }

    /**
     * @param string $className
     * @return mixed
     */
    public function convertClassName($className)
    {
        if (is_string($className) && array_key_exists($className, $this->getMap())) {
            return $this->getMap()[$className];
        }
        return $className;
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function getMap()
    {
        if ($this->map === null) {
            /** @var \DOMNodeList $renameNodes */
            /** @var \DOMElement $renameNode */
            /** @var \DOMElement $classNode */
            $renameNodes = $this->xml->query('/classmap/*');
            foreach ($renameNodes as $renameNode) {
                $map = ['from' => null, 'to' => null];
                foreach ($renameNode->childNodes as $classNode) {
                    if ($classNode->nodeName == 'from') {
                        $map['from'] = $classNode->nodeValue;
                    } else if ($classNode->nodeName == 'to') {
                        $map['to'] = $classNode->nodeValue ?: null;
                    }
                }
                if ($map['from']) {
                    $this->map[$map['from']] = $map['to'];
                }
            }
        }
        return $this->map;
    }
}
