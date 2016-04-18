<?php
/**
 * Created by PhpStorm.
 * User: dustinleblanc
 * Date: 4/16/16
 * Time: 11:07 AM
 */

namespace Pantheon\Terminus\Container;


use League\Container\Container;
use League\Container\Definition\DefinitionFactoryInterface;
use League\Container\Inflector\InflectorAggregateInterface;
use League\Container\ServiceProvider\ServiceProviderAggregateInterface;

class TerminusContainer extends Container
{
    public function __construct(
        ServiceProviderAggregateInterface $providers = null,
        InflectorAggregateInterface $inflectors = null,
        DefinitionFactoryInterface $definitionFactory = null
    )
    {
        parent::__construct($providers, $inflectors, $definitionFactory);
    }

    /**
     * Get a service.
     */
    public function get($alias, array $args = [])
    {
        return parent::get($alias, $args);
    }
}