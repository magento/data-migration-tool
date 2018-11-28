<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration;

use \Magento\Framework\App\Arguments\ValidationState;

/**
 * Class Config
 */
class Config
{
    const CONFIGURATION_FILE = 'config.xml';

    const CONFIGURATION_SCHEMA = 'config.xsd';

    const RESOURCE_TYPE_SOURCE = 'source';

    const RESOURCE_TYPE_DESTINATION = 'destination';

    const EDITION_MIGRATE_OPENSOURCE_TO_OPENSOURCE = 'opensource-to-opensource';

    const EDITION_MIGRATE_OPENSOURCE_TO_COMMERCE = 'opensource-to-commerce';

    const EDITION_MIGRATE_COMMERCE_TO_COMMERCE = 'commerce-to-commerce';

    const OPTION_AUTO_RESOLVE = 'auto_resolve';

    /**
     * @var \DOMXPath
     */
    protected $config;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var ValidationState
     */
    protected $validationState;

    /**
     * @param ValidationState $validationState
     */
    public function __construct(
        ValidationState $validationState
    ) {
        $this->validationState = $validationState;
        date_default_timezone_set('UTC');
    }

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
        $document = new \Magento\Framework\Config\Dom($xml, $this->validationState);

        if (!$document->validate($this->getConfigDirectoryPath() . self::CONFIGURATION_SCHEMA)) {
            throw new Exception('XML file is invalid.');
        }

        $this->config = new \DOMXPath($document->getDom());
        foreach ($this->config->query('//options/*') as $item) {
            $this->options[$item->nodeName] = $item->nodeValue;
        }
        return $this;
    }

    /**
     * Get Migration Tool Configuration Dir
     *
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
     * Get step data
     *
     * @param string $mode
     * @param string $name
     * @return array
     */
    public function getStep($mode, $name)
    {
        $step = [];
        /** @var \DOMNodeList $stepsDom */
        $stepsDom = $this->config->query("//steps[@mode='{$mode}']/step[@title='{$name}']");
        if ($stepsDom->length == 0) {
            return $step;
        }
        /** @var \DOMElement $stepDom */
        $stepDom = $stepsDom->item(0);
        /** @var \DOMElement $child */
        foreach ($stepDom->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $step[$child->nodeName] = $child->nodeValue;
            }
        }
        return $step;
    }

    /**
     * Returns configuration array for $resourceType connection
     *
     * @param string $resourceType type, one of two: self::CONNECTION_TYPE_SOURCE or self::CONNECTION_TYPE_DESTINATION
     * @return array
     */
    public function getResourceConfig($resourceType)
    {
        $this->validateResourceType($resourceType);
        $params = [];
        $sourceNode = $this->config->query('//' . $resourceType);
        if ($sourceNode->item(0)->attributes->getNamedItem('version')) {
            $params['version'] = $sourceNode->item(0)->attributes->getNamedItem('version')->nodeValue;
        }
        $source = $this->config->query('//' . $resourceType . '/*[1]');
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
     * Get source configuration
     *
     * @return array
     */
    public function getSource()
    {
        return $this->getResourceConfig(self::RESOURCE_TYPE_SOURCE);
    }

    /**
     * Get destination configuration
     *
     * @return array
     */
    public function getDestination()
    {
        return $this->getResourceConfig(self::RESOURCE_TYPE_DESTINATION);
    }

    /**
     * Validate resource type
     *
     * @param string $type
     * @throws Exception
     * @return void
     */
    protected function validateResourceType($type)
    {
        if (!in_array($type, [self::RESOURCE_TYPE_SOURCE, self::RESOURCE_TYPE_DESTINATION])) {
            throw new Exception('Unknown resource type: ' . $type);
        }
    }

    /**
     * Get option's value by name
     *
     * @param string $name
     * @return mixed
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Set value for option
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }
}
