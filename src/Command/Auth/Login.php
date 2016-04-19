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
use Symfony\Component\Console\Question\Question;

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
        $auth = $this->getContainer()->get('Auth');
        if ($this->callLoginAction($input, $auth, $output)) {
            return $output->writeln('<success>You have successfully logged in!</success>');
        } else {
            return $output->writeln(('<error>We were not able to successfully log you in.</error>'));
        }

    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Pantheon\Terminus\Services\Authentication $auth
     * @return bool|\Pantheon\Terminus\Services\Authentication
     * @throws \Pantheon\Terminus\Exceptions\TerminusException
     */
    protected function callLoginAction(InputInterface $input, Authentication $auth, OutputInterface $output)
    {
        return $this->tryTokenLogin($input, $auth, $output) ?: $this->tryEmailLogin($input, $auth, $output);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Pantheon\Terminus\Services\Authentication $auth
     * @return \Pantheon\Terminus\Services\Authentication
     */
    protected function tryTokenLogin(
      InputInterface $input,
      Authentication $auth,
      OutputInterface $output
    ) {
        if ($token = $input->getOption('machine-token')) {
            return $auth->loginViaMachineToken($token);
        } elseif ($email = $input->getArgument('email')) {
            if ($token = $auth->getTokenByEmail($email)) {
                return $auth->loginViaMachineToken($token);
            }
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Pantheon\Terminus\Services\Authentication $auth
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return bool
     */
    private function tryEmailLogin(InputInterface $input, Authentication $auth, OutputInterface $output)
    {
        $email = $input->getArgument('email') ?: $this->askForCredential($input, $output, 'email');
        $password = $input->getOption('password') ?: $this->askForCredential($input, $output, 'password');
        return $auth->logInViaUsernameAndPassword($email, $password);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $credential
     * @return mixed
     */
    private function askForCredential(InputInterface $input, OutputInterface $output, $credential = '')
    {
        $helper = $this->getHelper('question');
        switch ($credential) {
            case 'email':
                $hidden = false;
                $message = 'Please enter your email:';
                break;
            case 'password':
                $hidden = true;
                $message = 'Please enter your password:';
        }
        $question = new Question($message);
        $question->setHidden($hidden);
        $question->setHiddenFallback(false);

        return $helper->ask($input, $output, $question);
    }
}
