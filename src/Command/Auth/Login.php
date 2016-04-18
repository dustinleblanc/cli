<?php
namespace Pantheon\Terminus\Command\Auth;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Pantheon\Terminus\Command\TerminusCommand;
use Pantheon\Terminus\Exceptions\TerminusException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Pantheon\Terminus\Models\Auth;
use Pantheon\Terminus\Services\Request;

/**
 * Class Login
 *
 * Symfony Console Command to Autenticate with Pantheon platform.
 *
 * @package Pantheon\Terminus\Command\Auth
 */
class Login extends TerminusCommand
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('auth:login');
        $this->addArgument(
            'email',
            InputArgument::OPTIONAL,
            'The email address of the account you wish to login as.'
        )->addOption('password',
            null,
            InputOption::VALUE_OPTIONAL,
            'Log in non-interactively with this password. Useful for automation.'
        )->addOption(
            'machine-token',
            null,
            InputOption::VALUE_OPTIONAL,
            'Authenticates using a machine token from your dashboard. Stores the token for future use.'
        );

    }

    /**
     * Log in as a user
     *
     *  ## OPTIONS
     * [<email>]
     * : Email address to log in as.
     *
     * [--password=<value>]
     * : Log in non-interactively with this password. Useful for automation.
     *
     * [--machine-token=<value>]
     * : Authenticates using a machine token from your dashboard. Stores the
     *   token for future use.
     *
     * [--debug]
     * : dump call information when logging in.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logInViaMachineToken($input->getOption('machine-token'));
    }

    /**
     * Execute the login based on a machine token
     *
     * @param string[] $args Elements as follow:
     *   string token Machine token to initiate login with
     *   string email Email address to locate token with
     * @return bool True if login succeeded
     * @throws TerminusException
     */
    private function logInViaMachineToken($token) {
        $request = new Request();
        $options = [
            'form_params' => [
                'machine_token' => $token,
                'client'        => 'terminus',
            ],
            'method' => 'post',
        ];

        try {
            $response = $request->request(
                'authorize/machine-token',
                $options
            );
        } catch (\Exception $e) {
            throw new TerminusException(
                'The provided machine token is not valid.',
                [],
                1
            );
        }

        $data = $response['data'];
        $this->setInstanceData($response['data']);
        $user = Session::getUser();
        $user->fetch();
        $user_data = $user->serialize();
        if (isset($args['token'])) {
            $this->tokens_cache->add(
                ['email' => $user_data['email'], 'token' => $token,]
            );
        }
        return true;
    }

}
