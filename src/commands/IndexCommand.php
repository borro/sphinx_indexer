<?php
namespace aggardo\commands;

use aggardo\index\IndexerRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('index')
            ->setDescription('Start indexer')
            ->addArgument('indexes', InputArgument::IS_ARRAY, 'Indexes')
            ->addOption('rotate', 'r', InputOption::VALUE_NONE, 'Rotate indexes')
            ->addOption('indexer', 'i', InputOption::VALUE_OPTIONAL, 'Indexer path', 'indexer')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Indexer config', 'sphinx.conf')
            ->addOption('threads', 't', InputOption::VALUE_OPTIONAL, 'Count of threads indexer', 4);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pool = new \Pool($input->getOption('threads'), \Worker::class);
        foreach ($input->getArgument('indexes') as $index) {
            $pool->submit(new IndexerRunner(
                $input->getOption('indexer'),
                $input->getOption('config'),
                $index
            ));
        }
        $pool->shutdown();

        $pool->collect(function (IndexerRunner $work) use ($output) {
            $output->writeln($work->return);
        });
    }
}
