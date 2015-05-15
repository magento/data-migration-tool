<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration;

/**
 * Class Config
 */
class Config
{
    const CONFIGURATION_FILE = 'config.xml';

    const CONFIGURATION_SCHEMA = 'config.xsd';

    /**
     * @var \DOMXPath
     */
    protected $config;

    /**
     * @var array
     */
    protected $options;

    /**
     * Init configuration
     *
     * @param string $configFile
     * @return $this
     * @throws Exception
     */
    public function init($configFile = null)
    {
        if ($configFile === null) {
            $configFile = $this->getConfigDirectoryPath() . self::CONFIGURATION_FILE;
        }

        if (empty($configFile) || !file_exists($configFile)) {
            throw new Exception('Invalid config filename: '. $configFile);
        }

        $xml = file_get_contents($configFile);
        $document = new \Magento\Framework\Config\Dom($xml);

        if (!$document->validate($this->getConfigDirectoryPath() . self::CONFIGURATION_SCHEMA)) {
            throw new Exception('XML file is invalid.');
        }

        $this->config = new \DOMXPath($document->getDom());
        $this->options = null;
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
     * Get list of steps
     *
     * @param string $mode
     * @return array
     */
    public function getSteps($mode)
    {
        $steps = [];
        /** @var \DOMElement $item */
        foreach ($this->config->query("//steps[@mode='{$mode}']/step") as $item) {
            if (empty($item->attributes) || !$item->hasAttribute('title')) {
                continue;
            }
            $title = $item->getAttribute('title');
            /** @var \DOMElement $stage */
            foreach ($item->childNodes as $stage) {
                if ($stage->nodeType == XML_ELEMENT_NODE) {
                    $steps[$title][$stage->nodeName] = $stage->nodeValue;
                }
            }
        }
        return $steps;
    }

    /**
     * Get source configuration
     *
     * @return array
     */
    public function getSource()
    {
        $params = [];
        $sourceNode = $this->config->query('//source');
        if ($sourceNode->item(0)->attributes->getNamedItem('version')) {
            $params['version'] = $sourceNode->item(0)->attributes->getNamedItem('version')->nodeValue;
        }
        $source = $this->config->query('//source/*[1]');
        /** @var \DOMElement $item */
        foreach ($source as $item) {
            $params['type'] = $item->nodeName;
            $params[$item->nodeName] = [];
            /** @var \DOMNamedNodeMap $attribute */
            if ($item->hasAttributes()) {
                /** @var \DOMAttr $attribute */
                foreach ($item->attributes as $attribute) {
                    $params[$item->nodeName][$attribute->name] = $attribute->value;
                }
            }
        }
        return $params;
    }

    /**
     * Get destination configuration
     *
     * @return array
     */
    public function getDestination()
    {
        $params = [];
        $sourceNode = $this->config->query('//destination');
        if ($sourceNode->item(0)->attributes->getNamedItem('version')) {
            $params['version'] = $sourceNode->item(0)->attributes->getNamedItem('version')->nodeValue;
        }
        $source = $this->config->query('//destination/*[1]');
        /** @var \DOMElement $item */
        foreach ($source as $item) {
            $params['type'] = $item->nodeName;
            $params[$item->nodeName] = [];
            /** @var \DOMNamedNodeMap $attribute */
            if ($item->hasAttributes()) {
                /** @var \DOMAttr $attribute */
                foreach ($item->attributes as $attribute) {
                    $params[$item->nodeName][$attribute->name] = $attribute->value;
                }
            }
        }
        return $params;
    }

    /**
     * Get option's value by name
     *
     * @param string $name
     * @return mixed
     */
    public function getOption($name)
    {
        if ($this->options === null) {
            $this->options = [];
            foreach ($this->config->query('//options/*') as $item) {
                $this->options[$item->nodeName] = $item->nodeValue;
            }
        }

        return isset($this->options[$name]) ? $this->options[$name] : null;
    }
}
