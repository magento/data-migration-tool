<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\MapReader;

use Migration\Config;
use Migration\Exception;
use Migration\MapReaderInterface;

/**
 * Class MapReaderAbstract
 */
abstract class MapReaderAbstract implements MapReaderInterface
{
    const CONFIGURATION_SCHEMA = 'map.xsd';

    /**
     * @var \DOMXPath
     */
    protected $xml;

    /**
     * @var array
     */
    protected $ignoredDocuments = [];

    /**
     * @var array
     */
    protected $wildcards;

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
    }

    /**
     * Init configuration
     *
     * @param string $mapFile
     * @return $this
     * @throws Exception
     */
    protected function init($mapFile)
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
     * @inheritdoc
     */
    public function isFieldIgnored($document, $field, $type)
    {
        $this->validateType($type);
        $map = $this->xml->query(sprintf('//%s/field_rules/ignore/field[text()="%s.%s"]', $type, $document, $field));
        return $map->length > 0;
    }

    /**
     * @inheritdoc
     */
    public function isDocumentIgnored($document, $type)
    {
        $this->validateType($type);
        if (isset($this->ignoredDocuments[$type][$document])) {
            return true;
        }

        $map = $this->xml->query(sprintf('//%s/document_rules/ignore/document[text()="%s"]', $type, $document));
        $result = (($map->length > 0) || $this->isChangeLog($document)) ? true : false;
        if (!$result) {
            foreach ($this->getWildcards($type) as $documentWildCard) {
                $regexp = '/' . str_replace('*', '.+', $documentWildCard->nodeValue) . '/';
                $result = preg_match($regexp, $document) > 0;
                if ($result === true) {
                    break;
                }
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
            $searchExpression = sprintf('//%s/document_rules/ignore/document[contains (.,"*")]', $type);
            foreach ($this->xml->query($searchExpression) as $wildcard) {
                $this->wildcards[$type][] = $wildcard;
            }
        }
        return $this->wildcards[$type];
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function isFieldMapped($document, $field, $type)
    {
        if ($this->isFieldIgnored($document, $field, $type)) {
            return false;
        }
        /** @var \DOMNodeList $result */
        $result = $this->xml->query(sprintf('//source/field_rules/move/*[text()="%s.%s"]', $document, $field));
        return $result->length > 0;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getFieldMap($document, $field, $type)
    {
        $this->validateType($type);

        if ($this->isFieldIgnored($document, $field, $type)) {
            return false;
        }

        $documentMap = $this->getDocumentMap($document, $type);
        if ($documentMap !== false) {
            $result =  $documentMap . '.' . $field;
        } else {
            throw new Exception('Document has ambiguous configuration: ' . $document);
        }

        if (!$this->isFieldMapped($document, $field, $type)) {
            return explode('.', $result)[1];
        }

        $queryResult = $this->xml->query(
            sprintf('//source/field_rules/move/*[text()="%s.%s"]', $document, $field)
        );
        if ($queryResult->length > 0) {
            /** @var \DOMElement $node */
            foreach ($queryResult->item(0)->parentNode->childNodes as $node) {
                if ($node->nodeType == XML_ELEMENT_NODE && $node->nodeValue != $document . '.' . $field) {
                    $result = $node->nodeValue;
                }
            }
        }
        $this->validateFieldMap($result, $this->getOppositeType($type));
        return explode('.', $result)[1];
    }

    /**
     * @inheritdoc
     */
    public function getHandlerConfig($document, $field, $type)
    {
        $this->validateType($type);
        $config = [];

        $nodes = $this->xml->query(
            sprintf('//%s/field_rules/transform[field/text()="%s.%s"]/handler', $type, $document, $field)
        );
        /** @var \DOMElement $node */
        foreach ($nodes as $node) {
            $config['class'] = $node->getAttribute('class');
            $config['params'] = [];
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
     * @param array $documents
     * @return array
     */
    public function getDeltaDocuments($documents)
    {
        foreach ($documents as $document) {
            if ($this->isDocumentMaped($document, MapReaderInterface::TYPE_SOURCE)) {
                $queryResult = $this->xml
                    ->query(sprintf('//source/document_rules/log_changes/*[text()="%s"]', $document));
                if ($queryResult->length > 0 ) {
                    /** @var \DOMElement $document */
                    $document = $queryResult->item(0);
                    $result[$document->nodeValue] = $document->attributes->getNamedItem('key')->nodeValue;
                }
            }
        }
        return $result;
    }

    /**
     * @param string $type
     * @return bool
     * @throws Exception
     */
    protected function validateType($type)
    {
        if (!in_array($type, [MapReaderInterface::TYPE_SOURCE, MapReaderInterface::TYPE_DEST])) {
            throw new Exception('Unknown resource type: ' . $type);
        }
        return true;
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getOppositeType($type)
    {
        $this->validateType($type);
        return $type == MapReaderInterface::TYPE_SOURCE
            ? MapReaderInterface::TYPE_DEST
            : MapReaderInterface::TYPE_SOURCE;
    }

    /**
     * @param string $value
     * @param string $type
     * @return bool
     * @throws Exception
     */
    protected function validateFieldMap($value, $type)
    {
        $valueParts = explode('.', $value);
        if ($this->getDocumentMap($valueParts[0], $type) === false) {
            throw new Exception('Document has ambiguous configuration: ' . $valueParts[0]);
        }
        if ($this->isFieldIgnored($valueParts[0], $valueParts[1], $type)) {
            throw new Exception('Field has ambiguous configuration: ' . $value);
        }
        return true;
    }

    /**
     * Check if document is a change log item
     * @param string $document
     * @return bool
     */
    protected function isChangeLog($document)
    {
        $ignore = false;
        $clRegex = "/.+_cl_.+/";
        $changeLogMatches = [];
        preg_match($clRegex, $document, $changeLogMatches);
        if ($changeLogMatches) {
            $ignore = true;
        }
        return $ignore;
    }
}
