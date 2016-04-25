<?php

namespace Pantheon\Terminus\Services;


use GuzzleHttp\Psr7\Response;
use Interop\Container\ContainerInterface;
use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Services\Caches\TokensCache;
use Psy\Shell;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * The Authentication class is responsible for handling authentication actions with the Pantheon API.
 * @property TokensCache tokenCache
 */
class Authentication extends TerminusService
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $response;

    /**
     * @var Session
     */
    protected $session;

    protected $tokenCache;


    /**
     * Authentication constructor.
     * @param ContainerInterface $container
     * @param Request $request
     * @param Session $session
     */
    public function __construct(ContainerInterface $container = null, Request $request = null, Session $session = null, TokensCache $tokensCache = null)
    {
        parent::__construct($container);
        $this->request = $request ?: $this->getContainer()->get('Request');
        $this->session = $session ?: $this->getContainer()->get('Session');
        $this->tokenCache = $tokensCache ?: $this->getContainer()->get('TokenCache');
    }

    /**
     * Gets all email addresses for which there are saved machine tokens
     *
     * @return string[]
     */
    public function getAllSavedTokenEmails()
    {
        return $this->getContainer()
            ->get('TokenCache')
            ->getAllSavedTokenEmails();
    }

    /**
     * Generates the URL string for where to create a machine token
     *
     * @return string
     */
    public function getMachineTokenCreationUrl()
    {
        return sprintf(
            '%s://%s:%s/machine-token/create/%s',
            TERMINUS_PROTOCOL,
            TERMINUS_HOST,
            TERMINUS_PORT,
            gethostname()
        );
    }

    /**
     * Checks to see if the current user is logged in
     *
     * @return bool True if the user is logged in
     */
    public function loggedIn()
    {
        return (
            $this->getSession()->get('session')
            && $this->getSession()->getExpireTime() >= time()
        );
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param Session $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * Execute the login based on a machine token
     *
     * @param string $token
     * @return Authentication
     * @throws \Pantheon\Terminus\Exceptions\TerminusException
     */
    public function logInViaMachineToken($token = '')
    {
        $options = [
            'form_params' => [
                'machine_token' => $token,
                'client' => 'terminus',
            ],
            'method' => 'post',
        ];
        $this->setResponse($this->attemptLoginRequest($options));
        return $this;
    }

    /**
     * @return \Pantheon\Terminus\Models\User
     * @throws TerminusException
     */
    public function getCurrentUser()
    {
        try {
            return $this->getSession()->getUser();
        } catch (\Exception $e) {
            throw new TerminusException('Unable to retrieve user');
        }
    }

    /**
     * Execute the login via email/password
     *
     * @param string $email Email address associated with a Pantheon account
     * @param string $password Password for the account
     * @return $this
     * @throws InvalidArgumentException
     */
    public function logInViaUsernameAndPassword($email, $password)
    {
        if (!$this->isValidEmail($email)) {
            throw new \InvalidArgumentException('Email is invalid');
        }
        $options = [
            'form_params' => [
                'email' => $email,
                'password' => $password,
            ],
            'method' => 'post'
        ];
        $this->attemptLoginRequest($options);

        return $this;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Checks to see whether the email has been set with a machine token
     *
     * @param string $email Email address to check for
     * @return bool
     */
    public function tokenExistsForEmail($email)
    {
        return $this->getContainer()
            ->get('TokenCache')
            ->tokenExistsForEmail($email);
    }

    /**
     * @param $args
     * @return mixed
     */
    public function getTokenByEmail($email)
    {
        return $this->getTokenCache()
            ->findByEmail($email)['token'];
    }

    /**
     * @return TokensCache
     */
    public function getTokenCache()
    {
        return $this->tokenCache;
    }

    /**
     * @param mixed $tokenCache
     * @return Authentication
     */
    public function setTokenCache($tokenCache)
    {
        $this->tokenCache = $tokenCache;
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     * @throws TerminusException
     */
    protected function attemptLoginRequest(array $options = [])
    {
        try {
            $response = $this->request->request(
                'authorize/machine-token',
                $options
            );
        } catch (\Exception $e) {
            throw new TerminusException(
                'The provided credentials are not valid.',
                [],
                1
            );
        }
        $this->getSession()->setData($response);
        $this->setResponse($response);
        return $this;
    }

    /**
     * Checks whether email is in a valid or not
     *
     * @param string $email String to be evaluated for email address format
     * @return bool True if $email is in email address format
     */
    private function isValidEmail($email)
    {
        return !is_bool(filter_var($email, FILTER_VALIDATE_EMAIL));
    }

    /**
     * Saves the session data to a cookie
     *
     * @param \stdClass $data Session data to save
     * @return bool Always true
     */
    private function setInstanceData(\stdClass $data)
    {
        if (!isset($data->machine_token)) {
            $machine_token = (array)Session::instance()->get('machine_token');
        } else {
            $machine_token = $data->machine_token;
        }
        $session = [
            'user_uuid' => $data->user_id,
            'session' => $data->session,
            'session_expire_time' => $data->expires_at,
        ];
        if ($machine_token && is_string($machine_token)) {
            $session['machine_token'] = $machine_token;
        }
        Session::instance()->setData($session);
        return true;
    }
}
