<?php

namespace spec\Pantheon\Terminus\Services;

use League\Container\ContainerInterface;
use Pantheon\Terminus\Config\Config;
use Pantheon\Terminus\Services\Caches\TokensCache;
use Pantheon\Terminus\Services\Request;
use Pantheon\Terminus\Services\Session;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AuthenticationSpec extends ObjectBehavior
{
    function let(ContainerInterface $container, $request, $session, $tokenCache)
    {
        $request->beADoubleOf(Request::class);
        $session->beADoubleOf(Session::class);
        $session->getExpireTime()->willReturn(time() + 20);
        $session->get('session')->willReturn(true);
        $session->setData(null)->willReturn(true);
        $tokenCache->beADoubleOf(TokensCache::class);
        $tokenCache->findByEmail('foo@example.com')->willReturn('iamthetoken');
        $this->beConstructedWith($container, $request, $session, $tokenCache);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pantheon\Terminus\Services\Authentication');
    }

    function it_knows_if_you_are_logged_in($session)
    {
        $this->loggedIn()->shouldReturn(true);
    }

    function it_returns_self_when_login_attempted()
    {
        $this->loginViaMachineToken('foo')->shouldReturnAnInstanceOf('Pantheon\Terminus\Services\Authentication');
        $this->loginViaUsernameAndPassword('foo@bar.com', 'baz')->shouldReturnAnInstanceOf('Pantheon\Terminus\Services\Authentication');
    }

    function it_rejects_bad_emails()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('loginViaUsernameAndPassword',['not a valid email', 'password']);
    }

    function it_retrieves_stored_tokens_given_an_email()
    {
//        $this->getTokenByEmail()
    }
}
