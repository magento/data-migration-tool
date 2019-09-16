<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

use Migration\Exception;
use \Magento\Framework\App\Arguments\ValidationState;

/**
 * Class Map
 */
class Map implements MapInterface
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
    protected $ignoredFields = [];

    /**
     * @var array
     */
    protected $ignoredDataTypeFields = [];

    /**
     * @var array
     */
    protected $documentsMap = [];

    /**
     * @var array
     */
    protected $fieldsMap = [];

    /**
     * @var array
     */
    protected $wildcards;

    /**
     * @var ValidationState
     */
    protected $validationState;

    /**
     * @param ValidationState $validationState
     * @param string $mapFile
     * @throws Exception
     */
    public function __construct(
        ValidationState $validationState,
        $mapFile
    ) {
        $this->validationState = $validationState;
        $this->init($mapFile);
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

        $configFile = file_exists($mapFile) ? $mapFile : $this->getRootDir() . $mapFile;
        if (!is_file($configFile)) {
            throw new Exception('Invalid map filename: ' . $mapFile);
        }

        $xml = file_get_contents($configFile);
        $document = new \Magento\Framework\Config\Dom($xml, $this->validationState);

        if (!$document->validate($this->getRootDir() .'etc/' . self::CONFIGURATION_SCHEMA)) {
            throw new Exception('XML file is invalid.');
        }

        $this->xml = new \DOMXPath($document->getDom());
        return $this;
    }

    /**
     * Get Migration Tool Configuration Dir
     *
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
        $key = $document . '-' . $field . '-' . $type;
        if (isset($this->ignoredFields[$key])) {
            return $this->ignoredFields[$key];
        }
        $this->validateType($type);
        $map = $this->xml->query(sprintf('//%s/field_rules/ignore/field[text()="%s.%s"]', $type, $document, $field));
        $this->ignoredFields[$key] = ($map->length > 0);
        return $this->ignoredFields[$key];
    }

    /**
     * @inheritdoc
     */
    public function isFieldDataTypeIgnored($document, $field, $type)
    {
        $key = $document . '-' . $field . '-' . $type;
        if (isset($this->ignoredDataTypeFields[$key])) {
            return $this->ignoredDataTypeFields[$key];
        }
        $this->validateType($type);
        $map = $this->xml->query(sprintf('//%s/field_rules/ignore/datatype[text()="%s.%s"]', $type, $document, $field));
        $this->ignoredDataTypeFields[$key] = ($map->length > 0);
        return $this->ignoredDataTypeFields[$key];
    }

    /**
     * @inheritdoc
     */
    public function isDocumentIgnored($document, $type)
    {
        $this->validateType($type);
        $key = $document . '-' . $type;
        if (isset($this->ignoredDocuments[$key])) {
            return $this->ignoredDocuments[$key];
        }

        $map = $this->xml->query(sprintf('//%s/document_rules/ignore/document[text()="%s"]', $type, $document));
        $result = ($map->length > 0);
        if (!$result) {
            foreach ($this->getWildcards($type) as $documentWildCard) {
                $regexp = '/^' . str_replace('*', '.+', $documentWildCard->nodeValue) . '/';
                $result = preg_match($regexp, $document) > 0;
                if ($result === true) {
                    break;
                }
            }
        }
        $this->ignoredDocuments[$key] = $result;
        return $this->ignoredDocuments[$key];
    }

    /**
     * Get wildcards
     *
     * @param string $type
     * @return mixed
     */
    protected function getWildcards($type)
    {
        if ($this->wildcards === null || !isset($this->wildcards[$type])) {
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
    public function isDocumentMapped($document, $type)
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

        $key = $document . '-' . $type;
        if (isset($this->documentsMap[$key])) {
            return $this->documentsMap[$key];
        }
        $result = $document;
        if ($this->isDocumentMapped($document, $type)) {
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
        $this->documentsMap[$key] = $result;
        return $this->documentsMap[$key];
    }

    /**
     * @inheritdoc
     */
    public function getFieldMap($document, $field, $type)
    {
        $key = $document . '-' . $field . '-' . $type;
        if (isset($this->fieldsMap[$key])) {
            return $this->fieldsMap[$key];
        }

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
            $this->fieldsMap[$key] = explode('.', $result)[1];
            return $this->fieldsMap[$key];
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
        $this->fieldsMap[$key] = explode('.', $result)[1];

        return $this->fieldsMap[$key];
    }

    /**
     * @inheritdoc
     */
    public function getHandlerConfigs($document, $field, $type)
    {
        $this->validateType($type);
        $configs = [];

        $nodes = $this->xml->query(
            sprintf('//%s/field_rules/transform[field/text()="%s.%s"]/handler', $type, $document, $field)
        );
        
        /** @var \DOMElement $node */
        foreach ($nodes as $node) {
            $config = [
                'class'  => $node->getAttribute('class'),
                'params' => [],
            ];
            /** @var \DOMElement $param */
            foreach ($node->childNodes as $param) {
                if ($param->nodeType == XML_ELEMENT_NODE) {
                    $config['params'][$param->getAttribute('name')] = $param->getAttribute('value');
                }
            }

            $configs[]= $config;
        }

        return $configs;
    }

    /**
     * Validate type
     *
     * @param string $type
     * @return bool
     * @throws Exception
     */
    protected function validateType($type)
    {
        if (!in_array($type, [MapInterface::TYPE_SOURCE, MapInterface::TYPE_DEST])) {
            throw new Exception('Unknown resource type: ' . $type);
        }
        return true;
    }

    /**
     * Get Opposite Type
     *
     * @param string $type
     * @return string
     */
    protected function getOppositeType($type)
    {
        $this->validateType($type);
        return $type == MapInterface::TYPE_SOURCE
            ? MapInterface::TYPE_DEST
            : MapInterface::TYPE_SOURCE;
    }

    /**
     * Validate Field Map
     *
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
}
