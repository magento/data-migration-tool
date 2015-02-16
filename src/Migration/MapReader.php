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
    const CONFIGURATION_SCHEMA = 'map.xsd';

    const TYPE_SOURCE = 'source';
    const TYPE_DEST = 'destination';

    /**
     * @var \DOMXPath
     */
    protected $xml;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $ignoredDocuments = [];

    /**
     * @var array
     */
    protected $wildcards;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->init();
    }

    /**
     * Init configuration
     *
     * @return $this
     * @throws \Exception
     */
    public function init()
    {
        if (!is_null($this->xml)) {
            return $this;
        }

        $configFile = $this->getRootDir() . $this->config->getOption('map_file');
        if (!is_file($configFile)) {
            throw new \Exception('Invalid map filename: ' . $configFile);
        }

        $xml = file_get_contents($configFile);
        $document = new \Magento\Framework\Config\Dom($xml);

        if (!$document->validate($this->getRootDir() .'etc/' . self::CONFIGURATION_SCHEMA)) {
            throw new \Exception('XML file is invalid.');
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
        return dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $type
     * @param string $document
     * @param string $field
     * @return bool
     */
    public function isFieldIgnored($document, $field, $type)
    {
        $this->validateType($type);
        $map = $this->xml->query(sprintf('//%s/field_rules/ignore/field[text()="%s::%s"]', $type, $document, $field));
        return $map->length > 0;
    }

    /**
     * @param string $document
     * @param string $type
     * @return bool
     */
    public function isDocumentIgnored($document, $type)
    {
        $this->validateType($type);
        if (isset($this->ignoredDocuments[$type][$document])) {
            return true;
        }

        $map = $this->xml->query(sprintf('//%s/document_rules/ignore/document[text()="%s"]', $type, $document));
        $result = $map->length > 0;
        if (!$result) {
            foreach ($this->getWildcards($type) as $documentWildCard) {
                $regexp = '/' . str_replace('*', '.+', $documentWildCard->nodeValue) . '/';
                $result = preg_match($regexp, $document) > 0;
            }
        }
        if ($result) {
            $this->ignoredDocuments[$type][$document] = true;
        }
        return $result;
    }

    /**
     * @param string $type
     * @return mixed
     */
    protected function getWildcards($type)
    {
        if (is_null($this->wildcards) || !isset($this->wildcards[$type])) {
            $this->wildcards[$type] = [];
            foreach ($this->xml->query(sprintf('//%s/document_rules/ignore/document[contains (.,"*")]', $type)) as $wildcard) {
                $this->wildcards[$type][] = $wildcard;
            }
        }
        return $this->wildcards[$type];
    }

    /**
     * @param string $document
     * @param string $type
     * @return bool
     */
    public function isDocumentMaped($document, $type)
    {
        if ($this->isDocumentIgnored($document, $type)) {
            return false;
        }
        /** @var \DOMNodeList $result */
        $result = $this->xml->query(sprintf('//source/document_rules/rename/*[text()="%s"]', $document));
        return $result->length > 0;
    }

    /**
     * @param string $document
     * @param string $field
     * @param string $type
     * @return bool
     */
    public function isFieldMapped($document, $field, $type)
    {
        if ($this->isFieldIgnored($document, $field, $type)) {
            return false;
        }
        /** @var \DOMNodeList $result */
        $result = $this->xml->query(sprintf('//source/field_rules/move/*[text()="%s::%s"]', $document, $field));
        return $result->length > 0;
    }

    /**
     * @param string $document
     * @param string $type
     * @return bool|string
     */
    public function getDocumentMap($document, $type)
    {
        $this->validateType($type);

        if ($this->isDocumentIgnored($document, $type)) {
            return false;
        }

        $result = $document;
        if ($this->isDocumentMaped($document, $type)) {
            $queryResult = $this->xml->query(sprintf('//source/document_rules/rename/*[text()="%s"]', $document));
            if ($queryResult->length > 0) {
                /** @var \DOMElement $node */
                foreach ($queryResult->item(0)->parentNode->childNodes as $node) {
                    if ($node->nodeType == XML_ELEMENT_NODE && $node->nodeValue != $document) {
                        $result = $node->nodeValue;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param string $document
     * @param string $field
     * @param string $type
     * @return bool|string
     * @throws \Exception
     */
    public function getFieldMap($document, $field, $type)
    {
        $this->validateType($type);

        if ($this->isFieldIgnored($document, $field, $type)) {
            return false;
        }

        $documentMap = $this->getDocumentMap($document, $type);
        if ($documentMap !== false) {
            $result =  $documentMap . '::' . $field;
        } else {
            throw new \Exception('Document has ambiguous configuration: ' . $document);
        }

        if (!$this->isFieldMapped($document, $field, $type)) {
            return explode('::', $result)[1];
        }

        $queryResult = $this->xml->query(
            sprintf('//source/field_rules/move/*[text()="%s::%s"]', $document, $field)
        );
        if ($queryResult->length > 0) {
            /** @var \DOMElement $node */
            foreach ($queryResult->item(0)->parentNode->childNodes as $node) {
                if ($node->nodeType == XML_ELEMENT_NODE && $node->nodeValue != $document . '::' . $field) {
                    $result = $node->nodeValue;
                }
            }
        }
        $this->validateFieldMap($result, $this->getOppositeType($type));
        return explode('::', $result)[1];
    }

    /**
     * @param string $document
     * @param string $field
     * @param string $type
     * @return array
     */
    public function getHandlerConfig($document, $field, $type)
    {
        $this->validateType($type);
        $config = [];

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

        return $config;
    }

    /**
     * @param string $type
     * @return bool
     * @throws \Exception
     */
    public function validateType($type)
    {
        if (!in_array($type, [self::TYPE_SOURCE, self::TYPE_DEST])) {
            throw new \Exception('Unknown resource type: ' . $type);
        }
        return true;
    }

    /**
     * @param string $type
     * @return string
     * @throws \Exception
     */
    public function getOppositeType($type)
    {
        $this->validateType($type);
        return $type == self::TYPE_SOURCE ? self::TYPE_DEST : self::TYPE_SOURCE;
    }

    /**
     * @param string $value
     * @param string $type
     * @return bool
     * @throws \Exception
     */
    protected function validateFieldMap($value, $type)
    {
        $valueParts = explode('::', $value);
        if ($this->getDocumentMap($valueParts[0], $type) === false) {
            throw new \Exception('Document has ambiguous configuration: ' . $valueParts[0]);
        }
        if ($this->isFieldIgnored($valueParts[0], $valueParts[1], $type)) {
            throw new \Exception('Field has ambiguous configuration: ' . $value);
        }
        return true;
    }
}
