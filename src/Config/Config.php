<?php
/**
 * Created by PhpStorm.
 * User: dustinleblanc
 * Date: 4/18/16
 * Time: 11:50 AM
 */

namespace Pantheon\Terminus\Config;


use League\Container\ContainerInterface;

class Config
{
    /**
     * General Application configuration options.
     *
     * @var array
     */
    protected static $config = [];

    /**
     * Currently configured IOC Container.
     *
     * @var ContainerInterface|null
     */
    protected static $container;

    /**
     * @return array
     */
    public static function getConfig()
    {
        return self::$config;
    }

    /**
     * @param array $config
     */
    public static function setConfig($config)
    {
        self::$config = $config;
    }

    /**
     * @return mixed
     */
    public static function getContainer()
    {
        if (static::$container === null) {
            throw new \RuntimeException('container is not initialized yet. \Pantheon\Terminus\Config\Config::setContainer() must be called with a real container.');
        }
        return static::$container;
    }

    /**
     * @param mixed $container
     */
    public static function setContainer($container)
    {
        static::$container = $container;
    }

}
