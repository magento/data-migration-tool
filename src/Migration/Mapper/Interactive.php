<?php

namespace Migration\Mapper;

use Migration\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

class Interactive
{
    protected $output;

    protected $input;

    protected $helper;

    protected $ignoreAllDocuments;

    protected $ignoreAllFields;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $helper
     * @param $dataMode
     * @throws Exception
     */
    public function init(InputInterface $input, OutputInterface $output, $helper, $dataMode)
    {
        $this->output = $output;
        $this->input = $input;
        $this->helper = $helper;

        $output->writeln([PHP_EOL, '<info>Unmapped/missing documents and/or fields have been found.</info>']);

        $question = new ConfirmationQuestion('<question>Do you want to interactively map the missing/unmapped documents & fields?</question> <comment>[yes]</comment> ', true);
        if (!$this->helper->ask($this->input, $this->output, $question)) {
            return;
        }

        $mapFilename = $this->helper->ask($this->input, $this->output, new Question('<question>Path to the map.xml.dist of your Magento 1 version</question> <comment>i.e.: ./etc/[ce/ee]-to-[ce/ee]/[version/map.xml.dist</comment> '));
        if (!$mapFilename || !file_exists($mapFilename)) {
            throw new Exception('Invalid map filename: ' . $mapFilename);
        }

        // Loop through the unmapped destination documents, ask what to do (ignore all/ignore/rename)
        $ignoreDocuments = ['destination' => [], 'source' => []];
        $renameDocuments = ['destination' => [], 'source' => []];
        foreach ($dataMode->notMappedDocuments as $direction => $documents) {
            $direction = ($direction ? 'destination' : 'source');
            foreach (array_keys($documents) as $document) {
                switch ($this->askActionDocument($document, $direction)):
                    case 'ignore':
                        $ignoreDocuments[$direction][] = $document;
                        break;
                    case 'rename':
                        $to = $this->helper->ask($this->input, $this->output, new Question('<question>What do you want to rename ' . $direction . ' document ' . $document . ' to ?</question>'));
                        $renameDocuments[$direction][] = ['document' => $document, 'to' => $to];
                        break;
                endswitch;
            }
        }

        // Loop through the unmapped destination fields, ask what to do (ignore all/ignore/move/transform)
        $ignoreDocumentFields = ['destination' => [], 'source' => []];
        $moveDocumentFields = ['destination' => [], 'source' => []];
        $transformDocumentFields = ['destination' => [], 'source' => []];

        foreach ($dataMode->notMappedDocumentFields as $direction => $documentFields) {
            foreach ($documentFields as $document => $fields) {
                foreach ($fields as $field) {
                    $documentField = $document . '.' . $field;
                    switch ($this->askActionField($documentField, $direction)):
                        case 'ignore':
                            $ignoreDocumentFields[$direction][] = $documentField;
                            break;
                        case 'move':
                            $to = $this->helper->ask($this->input, $this->output, new Question('<question>What do you want to move the contents of ' . $direction . ' field ' . $documentField . ' to ?</question>'));
                            $moveDocumentFields[$direction][] = ['field' => $documentField, 'to' => $to];
                            break;
                        case 'transform':
                            $handler = 'UNKOWN'; // @todo implement
                            $transformDocumentFields[$direction][] = ['field' => $documentField, 'handler' => $handler];
                    endswitch;
                }
            }
        }

        // Append documents/fields to XML
        $mapXml = simplexml_load_file($mapFilename);
        foreach ($mapXml->children() as $direction => $directionChilds) {
            foreach ($directionChilds->children() as $rule => $ruleChilds) {
                if ((string)$rule == 'document_rules') {
                    foreach ($ignoreDocuments[$direction] as $ignoreDocument) {
                        $ignore = $ruleChilds->addChild('ignore');
                        $ignore->addChild('document', $ignoreDocument);
                    }

                    foreach ($renameDocuments[$direction] as $renameDocument) {
                        $rename = $ruleChilds->addChild('rename');
                        $rename->addChild('document', $renameDocument['document']);
                        $rename->addChild('to', $renameDocument['to']);
                    }
                }

                if ((string)$rule == 'field_rules') {
                    foreach ($ignoreDocumentFields[$direction] as $ignoreDocumentField) {
                        $ignore = $ruleChilds->addChild('ignore');
                        $ignore->addChild('field', $ignoreDocumentField);
                    }

                    foreach ($moveDocumentFields[$direction] as $moveDocumentField) {
                        $move = $ruleChilds->addChild('rename');
                        $move->addChild('field', $moveDocumentField['field']);
                        $move->addChild('to', $moveDocumentField['to']);
                    }

                    foreach ($transformDocumentFields[$direction] as $transformDocumentField) {
                        $transform = $ruleChilds->addChild('transform');
                        $transform->addChild('field', $transformDocumentField['field']);
                        $transform->addChild('handler'); // @todo implemnet
                    }
                }
            }
        }

        // Format XML
        $dom = new \DOMDocument("1.0");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($mapXml->asXML());

        // Save XML
        $mapOutputFilename = $this->helper->ask($this->input, $this->output, new Question('<question>To which file do you want to save the mapping?</question> <comment>map.xml</comment> ', 'map.xml'));
        if ($mapOutputFilename) {
            $dom->save($mapOutputFilename);
            $this->output->writeln('<info>Mapping succesfully written to </info><comment>' . $mapOutputFilename . '</comment>');
        }
    }

    /**
     * Ask what action to perform on current documentc
     *
     * @param $document
     * @param $direction
     * @return string
     */
    private function askActionDocument($document, $direction)
    {
        if (is_null($this->ignoreAllDocuments)) {
            $question = new ConfirmationQuestion('<question>Do you want to ignore all documents?</question> <comment>[no]</comment> ', false);
            $this->ignoreAllDocuments = $this->helper->ask($this->input, $this->output, $question);
        }
        
        if ($this->ignoreAllDocuments) {
            return 'ignore';
        }

        return $this->helper->ask($this->input, $this->output, new ChoiceQuestion('<question>What action do you want to perform on ' . $direction . ' document ' . $document . '</question> <comment>[ignore]</comment> ', ['ignore', 'rename'], 0));
    }

    /**
     * Ask what action to perform on current field in the mapping stage
     *
     * @param $field
     * @param $direction
     * @return string
     */
    private function askActionField($field, $direction)
    {
        if (is_null($this->ignoreAllFields)) {
            $question = new ConfirmationQuestion('<question>Do you want to ignore all fields?</question> <comment>[no]</comment> ', false);
            $this->ignoreAllFields = $this->helper->ask($this->input, $this->output, $question);
        }

        if ($this->ignoreAllFields) {
            return 'ignore';
        }

        return $this->helper->ask($this->input, $this->output, new ChoiceQuestion('<question>What action do you want to perform on '. $direction . ' field ' . $field . '</question> <comment>[ignore]</comment> ', ['ignore', 'move', 'transform'], 0));
    }

}