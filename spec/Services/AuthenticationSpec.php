<?php

namespace spec\Pantheon\Terminus\Services;

use League\Container\ContainerInterface;
use Pantheon\Terminus\Config\Config;
use Pantheon\Terminus\Services\Request;
use Pantheon\Terminus\Services\Session;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AuthenticationSpec extends ObjectBehavior
{
    function let(ContainerInterface $container, $request, $session)
    {
        $request->beADoubleOf(Request::class);
        $session->beADoubleOf(Session::class);
        $session->getExpireTime()->willReturn(time() + 20);
        $session->get('session')->willReturn(true);

        $this->beConstructedWith($container, $request, $session);

    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pantheon\Terminus\Services\Authentication');
    }

    function it_knows_if_you_are_logged_in($session)
    {
        $this->loggedIn()->shouldReturn(true);
    }
}
