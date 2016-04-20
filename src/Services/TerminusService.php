<?php
/**
 * Created by PhpStorm.
 * User: dustinleblanc
 * Date: 4/18/16
 * Time: 10:57 AM
 */

namespace Pantheon\Terminus\Services;


use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use League\Container\ContainerInterface;
use Pantheon\Terminus\Config\Config;

abstract class TerminusService implements ContainerAwareInterface
{
    use ContainerAwareTrait;


    /**
     * TerminusService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container ?: Config::getContainer();
    }
}
