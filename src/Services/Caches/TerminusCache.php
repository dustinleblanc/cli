<?php
/**
 * Created by PhpStorm.
 * User: dustinleblanc
 * Date: 4/18/16
 * Time: 10:40 AM
 */

namespace Pantheon\Terminus\Services\Caches;


use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;

abstract class TerminusCache implements ContainerAwareInterface
{
    use ContainerAwareTrait;
}
