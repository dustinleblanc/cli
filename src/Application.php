<?php
namespace Pantheon\Terminus;


use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Pantheon\Terminus\Command\ArtCommand;
use Pantheon\Terminus\Command\Auth\Login;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Yaml\Yaml;

class Application extends SymfonyApplication implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $workingDir;

    /**
     * Application constructor.
     * @param string $name
     * @param string $version
     * @param $workingDir
     */
    public function __construct($name = '', $version = '', $workingDir = '')
    {
        parent::__construct($name, $version);
        $this->workingDir = $workingDir;
        $this->defineConstants();
    }

    /**
     * Sets constants necessary for the proper functioning of Terminus
     *
     * @return void
     */
    private function defineConstants()
    {
        if (!defined('TERMINUS_ROOT')) {
            define('TERMINUS_ROOT', $this->getTerminusRoot());
        }
        if (!defined('Terminus')) {
            define('Terminus', true);
        }
        $default_constants = Yaml::parse(
            file_get_contents(TERMINUS_ROOT . '/config/constants.yml')
        );
        foreach ($default_constants as $var_name => $default) {
            if (!defined($var_name)) {
                if (isset($_SERVER[$var_name]) && ($_SERVER[$var_name] != '')) {
                    define($var_name, $_SERVER[$var_name]);
                } else if (!defined($var_name)) {
                    define($var_name, $default);
                }
            }
        }
        date_default_timezone_set(TERMINUS_TIME_ZONE);
        if (!defined('TERMINUS_SCRIPT')) {
            define('TERMINUS_SCRIPT', $this->getTerminusScript());
        }
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

    /**
     * Return all Terminus commands from all scopes (stubbed).
     * @return array
     */
    public function getAllCommands()
    {
        return [
            new ArtCommand,
            new Login
        ];
    }

    /**
     * Imports environment variables
     *
     * @return void
     */
    private function importEnvironmentVariables()
    {
        //Load environment variables from __DIR__/.env
        if (file_exists(getcwd() . '/.env')) {
            $env = new \Dotenv\Dotenv(getcwd());
            $env->load();
        }
    }
}
