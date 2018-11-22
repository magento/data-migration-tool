<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

use Migration\Config;
use Migration\Exception;
use \Magento\Framework\App\Arguments\ValidationState;

/**
 * Class Settings
 */
class Settings
{
    /**
     * Option in config file, which indicates the location of map file for settings
     */
    const MAP_FILE_OPTION = 'settings_map_file';

    /**
     * XSD schema of configuration file
     */
    const CONFIGURATION_SCHEMA = 'settings.xsd';

    /**
     * Configuration of application
     *
     * @var Config
     */
    protected $config;

    /**
     * Settings map
     *
     * @var \DOMXPath
     */
    protected $xml;

    /**
     * Saving ignored nodes
     *
     * @var array
     */
    protected $ignoredNodes = [];

    /**
     * Saving mapped nodes
     *
     * @var array
     */
    protected $mappedNodes = [];

    /**
     * Saving handlers for nodes
     *
     * @var array
     */
    protected $nodeHandle = [];

    /**
     * Saving wildcards, present in configuration
     *
     * @var array
     */
    protected $wildcards = [];

    /**
     * @var ValidationState
     */
    protected $validationState;

    /**
     * @param Config $config
     * @param ValidationState $validationState
     */
    public function __construct(
        Config $config,
        ValidationState $validationState
    ) {
        $this->config = $config;
        $this->validationState = $validationState;
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
        $document = new \Magento\Framework\Config\Dom($xml, $this->validationState);

        if (!$document->validate($rootDir .'etc/' . self::CONFIGURATION_SCHEMA)) {
            throw new Exception('XML file is invalid.');
        }

        $this->xml = new \DOMXPath($document->getDom());
        return $this;
    }

    /**
     * Parsing, saving and returning the list of wildcards in map file
     *
     * @return array
     */
    protected function getWildcards()
    {
        if (empty($this->wildcards)) {
            $searchExpression = '//key/ignore/path[contains (.,"*")]';
            foreach ($this->xml->query($searchExpression) as $wildcard) {
                $this->wildcards[] = $wildcard;
            }
        }
        return $this->wildcards;
    }

    /**
     * Check if the setting should be ignored
     *
     * @param string $path
     * @return bool
     */
    public function isNodeIgnored($path)
    {
        if (isset($this->ignoredNodes[$path])) {
            return $this->ignoredNodes[$path];
        }

        $map = $this->xml->query(sprintf('//key/ignore/path[text()="%s"]', $path));
        $result = ($map->length > 0);
        if (!$result) {
            foreach ($this->getWildcards() as $settingWildcard) {
                $regexp = str_replace("/", "\\/", $settingWildcard->nodeValue);
                $regexp = '/^' . str_replace('*', '.+', $regexp) . '/';
                $result = preg_match($regexp, $path) > 0;
                if ($result === true) {
                    break;
                }
            }
        }

        $this->ignoredNodes[$path] = $result;
        return $this->ignoredNodes[$path];
    }

    /**
     * Check of node is mapped
     *
     * @param string $path
     * @return bool
     */
    public function isNodeMapped($path)
    {
        if ($this->isNodeIgnored($path)) {
            return false;
        }
        $map = $this->xml->query(sprintf('//key/rename/path[text()="%s"]', $path));
        return $map->length > 0;
    }

    /**
     * Getting the name of the path in the destination database
     *
     * @param string $path
     * @return string
     */
    public function getNodeMap($path)
    {
        if (isset($this->mappedNodes[$path])) {
            return $this->mappedNodes[$path];
        }
        $this->mappedNodes[$path] = $path;
        if ($this->isNodeMapped($path)) {
            $queryResult = $this->xml->query(sprintf('//key/rename/path[text()="%s"]', $path));
            if ($queryResult->length > 0) {
                /** @var \DOMElement $node */
                foreach ($queryResult->item(0)->parentNode->childNodes as $node) {
                    if (($node->nodeType == XML_ELEMENT_NODE) && ($node->nodeName == 'to')) {
                        $this->mappedNodes[$path] = $node->nodeValue;
                        break;
                    }
                }
            }
        }
        return $this->mappedNodes[$path];
    }

    /**
     * Getting the value handler for given path
     *
     * @param string $path
     * @return bool|string
     */
    public function getValueHandler($path)
    {
        if (isset($this->nodeHandle[$path])) {
            return $this->nodeHandle[$path];
        }
        if ($this->isNodeIgnored($path)) {
            return false;
        }

        $this->nodeHandle[$path] = false;
        $queryResult = $this->xml->query(sprintf('//value/transform/path[text()="%s"]', $path));
        if ($queryResult->length > 0) {
            /** @var \DOMElement $node */
            foreach ($queryResult->item(0)->parentNode->childNodes as $node) {
                if (($node->nodeType == XML_ELEMENT_NODE) && ($node->nodeName == 'handler')) {
                    $handler['class'] = $node->getAttribute('class');
                    $handler['params'] = [];
                    /** @var \DOMElement $param */
                    foreach ($node->childNodes as $param) {
                        if ($param->nodeType == XML_ELEMENT_NODE) {
                            $handler['params'][$param->getAttribute('name')] = $param->getAttribute('value');
                        }
                    }
                    $this->nodeHandle[$path] = $handler;
                    break;
                }
            }
        }
        return $this->nodeHandle[$path];
    }
}
