<?php
/**
 * Created by PhpStorm.
 * User: dustinleblanc
 * Date: 4/18/16
 * Time: 10:57 AM
 */

namespace Pantheon\Terminus\Services;


use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Services\Request;

/**
 *
 * @property mixed session
 */
class Authentication extends TerminusService
{
    protected $request;
    protected $session;


    /**
     * Authentication constructor.
     * @param Request $request
     */
    public function __construct(Request $request = null, Session $session = null)
    {
        parent::__construct();
        $this->request = $request ?: $this->getContainer()->get('Request');
        $this->session = $session ?: $this->getContainer()->get('Session');
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
          isset($this->session->session)
          && (
            Utils\isTest()
            || ($session->session_expire_time >= time())
          )
        );
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
        return $this->attemptLoginRequest($options);
    }

    /**
     * @param Array $options
     * @return Array Response
     *      @see \Pantheon\Terminus\Services\Request::request().
     * @throws TerminusException
     */
    protected function attemptLoginRequest(Array $options = [])
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
        return $response;
    }

    /**
     * Saves the session data to a cookie
     *
     * @param \stdClass $data Session data to save
     * @return bool Always true
     */
    private
    function setInstanceData(
      \stdClass $data
    ) {
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

    /**
     * Execute the login via email/password
     *
     * @param string $email Email address associated with a Pantheon account
     * @param string $password Password for the account
     * @return bool True if login succeeded
     * @throws TerminusException
     */
    public
    function logInViaUsernameAndPassword(
      $email,
      $password
    ) {
        $options = [
          'form_params' => [
            'email' => $email,
            'password' => $password,
          ],
          'method' => 'post'
        ];
        $this->attemptLoginRequest($options);

        $this->setInstanceData($response['data']);
        return true;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Checks whether email is in a valid or not
     *
     * @param string $email String to be evaluated for email address format
     * @return bool True if $email is in email address format
     */
    private function isValidEmail($email) {
        $is_email = !is_bool(filter_var($email, FILTER_VALIDATE_EMAIL));
        return $is_email;
    }

    /**
     * Checks to see whether the email has been set with a machine token
     *
     * @param string $email Email address to check for
     * @return bool
     */
    public function tokenExistsForEmail($email) {
        $file_exists = $this->tokens_cache->tokenExistsForEmail($email);
        return $file_exists;
    }

    /**
     * @param $args
     * @return mixed
     */
    public function getTokenByEmail($args) {
        return $this->getContainer()
          ->get('TokenCache')
          ->findByEmail($args['email'])['token'];
    }

    /**
     * @param $response
     */
    public function updateTokenCache($response)
    {
        $this->setInstanceData($response['data']);
        $user = $this->getContainer()->get('Session')::getUser();
        $user->fetch();
        $user_data = $user->serialize();
        $this->getContainer()
          ->get('TokenCache')
          ->add([
            'email' => $user_data['email'],
            'token' => $user_data['token']
          ]);
    }
}
