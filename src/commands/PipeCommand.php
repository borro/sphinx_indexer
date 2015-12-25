<?php
namespace aggardo\commands;

use aggardo\pipe\DiscoveryDirectory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PipeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('pipe')
            ->setDescription('Command put xml to stdout from csv files in folder')
            ->addArgument('folder', InputArgument::REQUIRED, 'Folder with csv files')
            ->addOption('pattern', 'p', InputOption::VALUE_OPTIONAL, 'Set PCRE pattern for search files')
            ->addOption('delimiter', 'd', InputOption::VALUE_OPTIONAL, 'Set the field delimiter (one character only).', ';')
            ->addOption('enclosure', 'e', InputOption::VALUE_OPTIONAL, 'Set the field enclosure character (one character only).', '"')
            ->addOption('escape', 'E', InputOption::VALUE_OPTIONAL, 'Set the escape character (one character only)', '"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = new DiscoveryDirectory($input->getArgument('folder'), $input->getOption('pattern'));
        $xml = new \XMLWriter();
        $xml->openURI('php://output');
        $xml->setIndent(true);
        $xml->setIndentString("\t");
        $output->writeln('<?xml version="1.0" encoding="utf-8"?>');
        $xml->startElement('sphinx:docset');

        $xml->startElement('sphinx:schema');
        $xml->startElement('sphinx:field');
        $xml->writeAttribute('name', 'subcategory');
        $xml->endElement();
        $xml->startElement('sphinx:attr');
        $xml->writeAttribute('name', 'subcategory');
        $xml->writeAttribute('type', 'string');
        $xml->endElement();
        $xml->startElement('sphinx:attr');
        $xml->writeAttribute('name', 'category-id');
        $xml->writeAttribute('type', 'bigint');
        $xml->endElement();
        $xml->startElement('sphinx:attr');
        $xml->writeAttribute('name', 'info-hash');
        $xml->writeAttribute('type', 'string');
        $xml->endElement();
        $xml->startElement('sphinx:field');
        $xml->writeAttribute('name', 'name');
        $xml->endElement();
        $xml->startElement('sphinx:attr');
        $xml->writeAttribute('name', 'name');
        $xml->writeAttribute('type', 'string');
        $xml->endElement();
        $xml->startElement('sphinx:attr');
        $xml->writeAttribute('name', 'size');
        $xml->writeAttribute('type', 'bigint');
        $xml->endElement();
        $xml->startElement('sphinx:attr');
        $xml->writeAttribute('name', 'date');
        $xml->writeAttribute('type', 'timestamp');
        $xml->endElement();
        $xml->endElement();

        $documents_id = [];
        foreach ($directory->getFiles() as $file) {
            if ($file->isFile()) {
                $csv = $file->openFile();
                $csv->setCsvControl(
                    $input->getOption('delimiter'),
                    $input->getOption('enclosure'),
                    $input->getOption('escape')
                );
                $csv->setFlags(\SplFileObject::READ_CSV);
                foreach ($csv as $row) {
                    if (count($row) > 1 && !in_array($row[2], $documents_id, true)) {
                        $documents_id[] = $row[2];
                        $xml->startElement('sphinx:document');
                        $xml->writeAttribute('id', $row[2]);
                        $xml->writeElement('subcategory', $row[1]);
                        $xml->writeElement('category-id', $row[0]);
                        $xml->writeElement('info-hash', $row[3]);
                        $xml->writeElement('name', $row[4]);
                        $xml->writeElement('size', $row[5]);
                        $xml->writeElement('date', array_key_exists(6, $row) ? strtotime($row[6]) : 0);
                        $xml->endElement();
                    }
                }
                $xml->flush();
            }
        }
        $xml->endElement();
        $xml->flush();
    }
}
