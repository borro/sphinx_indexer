<?php

namespace aggardo\index;

class IndexerRunner extends \Stackable
{
    /**
     * @var string
     */
    private $indexer;
    /**
     * @var string
     */
    private $config;
    /**
     * @var string
     */
    private $index;
    /**
     * @var string
     */
    public $return;

    public function __construct($indexer, $config, $index)
    {
        $this->indexer = $indexer;
        $this->config = $config;
        $this->index = $index;
    }

    public function run()
    {
        $command = sprintf('%s --config %s %s', $this->indexer, $this->config, $this->index);
        echo 'Try to run command: ', $command, PHP_EOL;
        $this->return = shell_exec($command);
        echo $this->return;
    }
}
