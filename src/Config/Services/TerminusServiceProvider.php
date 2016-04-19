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
        'Request',
        'Session',
        'Workflows',
        'Instruments',
        'MachineTokens',
        'SshKeys',
        'TokenCache',
        'UserOrganizationMemberships'
    ];

    public function register()
    {
        $this->getContainer()->add('Auth', \Pantheon\Terminus\Services\Authentication::class);
        $this->getContainer()->add('Logger', Monolog::class);
        $this->getContainer()->add('Request', \Pantheon\Terminus\Services\Request::class);
        $this->getContainer()->add('Session', \Pantheon\Terminus\Services\Session::class);
        $this->getContainer()->add('Workflows', \Pantheon\Terminus\Models\Workflow::class);
        $this->getContainer()->add('Instruments', \Pantheon\Terminus\Models\Instrument::class);
        $this->getContainer()->add('MachineTokens', \Pantheon\Terminus\Models\MachineToken::class);
        $this->getContainer()->add('SshKeys', \Pantheon\Terminus\Models\SshKey::class);
        $this->getContainer()->add('TokenCache', \Pantheon\Terminus\Services\Caches\TokensCache::class);
        $this->getContainer()->add('UserOrganizationMemberships', \Pantheon\Terminus\Models\UserOrganizationMembership::class);
    }
}
