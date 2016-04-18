<?php
/**
 * Created by PhpStorm.
 * User: dustinleblanc
 * Date: 4/16/16
 * Time: 8:28 PM
 */

namespace Pantheon\Terminus\Config\Services;


use League\Container\ServiceProvider\AbstractServiceProvider;

class TerminusServiceProvider extends AbstractServiceProvider
{
    protected $provides = [
        'Auth',
        'Request'
    ];

    public function register()
    {
        $this->getContainer()->add('Auth', \Pantheon\Terminus\Services\Authentication::class);
        $this->getContainer()->add('Request', \Pantheon\Terminus\Services\Request::class);

    }
}
