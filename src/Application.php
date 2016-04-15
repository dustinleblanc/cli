<?php
namespace Pantheon\Terminus;


use Symfony\Component\Console\Application as SymfonyApplication;

use League\Container\ContaierAwareInterface;
use League\Container\ContainerAwareTrait;

class Application extends SymfonyApplication
{
    use ContainerAwareTrait;

    private $workingDir;

    public function __construct($name, $version, $workingDir)
    {
        parent::__construct($name, $version);
        $this->workingDir = $workingDir;
    }

    /**
     * @return mixed
     */
    public function getWorkingDir()
    {
        return $this->workingDir;
    }

    /**
     * @param mixed $workingDir
     */
    public function setWorkingDir($workingDir)
    {
        $this->workingDir = $workingDir;
    }
}
