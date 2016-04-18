<?php
namespace Pantheon\Terminus\Command\Auth;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Pantheon\Terminus\Command\TerminusCommand;
use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Services\Authentication;
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
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = [
            'token' => $input->getOption('machine-token'),
            'email' => $input->getArgument('email')
        ];
        $auth = new Authentication();
        $auth->logInViaMachineToken($options);
    }
}
