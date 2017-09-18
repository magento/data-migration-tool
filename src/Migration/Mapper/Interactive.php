<?php

namespace Migration\Mapper;

use Migration\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class Interactive
{

    public function init(InputInterface $input, OutputInterface $output, $helper, $dataMode)
    {
        $output->writeln([PHP_EOL, '<info>Unmapped documents and/or fields have been found.</info>']);

        $question = new ConfirmationQuestion('<question>Do you want to interactively map the unmapped documents & fields?</question> <comment>[yes]</comment> ', true);
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $mapFilename = $helper->ask($input, $output, new Question('<question>Path to the map.xml.dist of your Magento 1 version</question> <comment>i.e.: ./etc/[ce/ee]-to-[ce/ee]/[version/map.xml.dist</comment> '));
        if (!$mapFilename || !file_exists($mapFilename)) {
            throw new Exception('Invalid map filename: ' . $mapFilename);
        }

        // Start looping through the unmapped documents and fields, ask what to do (ignore all/ignore/move/transform) and add to XML

        // Save XML

    }

}