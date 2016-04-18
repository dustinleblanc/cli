<?php
namespace Pantheon\Terminus;


use Dotenv\Dotenv;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use League\Container\ContainerInterface;
use Pantheon\Terminus\Command\ArtCommand;
use Pantheon\Terminus\Command\Auth\Login;
use Pantheon\Terminus\Config\Config;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Application
 *
 * Base Symfony application that encapsulates Terminus Command Line Utility.
 * @package Pantheon\Terminus
 */
class Application extends SymfonyApplication implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Application constructor.
     * @param string $name
     * @param string $version
     * @param $workingDir
     */
    public function __construct(
      $name = '',
      $version = '',
      $workingDir = '',
      ContainerInterface $container = null
    ) {
        parent::__construct($name, $version);
        $this->defineConstants();
        // Store the container in our config object if it was provided.
        if ($container != null) {
            Config::setContainer($container);
        }
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
     * Finds and returns the root directory of Terminus
     *
     * @param string $current_dir Directory to start searching at
     * @return string
     * @throws TerminusError
     */
    private function getTerminusRoot($current_dir = null)
    {
        if (is_null($current_dir)) {
            $current_dir = dirname(__DIR__);
        }
        if (file_exists("$current_dir/composer.json")) {
            return $current_dir;
        }
        $dir = explode('/', $current_dir);
        array_pop($dir);
        if (empty($dir)) {
            throw new TerminusError("Could not locate root to set TERMINUS_ROOT.");
        }
        $dir = implode('/', $dir);
        $root_dir = $this->getTerminusRoot($dir);
        return $root_dir;
    }

    /**
     * Finds and returns the name of the script running Terminus functions
     *
     * @return string
     */
    private function getTerminusScript()
    {
        $debug = debug_backtrace();
        $script_location = array_pop($debug);
        $script_name = str_replace(
            TERMINUS_ROOT . '/',
            '',
            $script_location['file']
        );
        return $script_name;
    }

    /**
     * Retrieve all available Terminus commands. (stubbed)
     * @return array
     *    A collection of Terminus commands.
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
     * Load environment variables from __DIR__/.env
     *
     * @return void
     */
    private function importEnvironmentVariables()
    {
        if (file_exists(getcwd() . '/.env')) {
            $env = new Dotenv(getcwd());
            $env->load();
        }
    }
}
