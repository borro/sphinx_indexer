<?php

namespace aggardo\pipe;

class DiscoveryDirectory
{
    /**
     * @var string
     */
    private $directory;
    /**
     * @var null|string
     */
    private $regex;

    public function __construct($directory, $regex = null)
    {
        $this->directory = $directory;
        $this->regex = $regex;
    }

    /**
     * @return \SplFileInfo[]
     */
    public function getFiles()
    {
        $directory = new \RecursiveDirectoryIterator($this->directory);
        $iterator = new \RecursiveIteratorIterator($directory);
        if ($this->regex) {
            $iterator = new \RegexIterator($iterator, sprintf('/%s/i', $this->regex));
        }
        return $iterator;
    }
}
