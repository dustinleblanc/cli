<?php
/**
 * Created by PhpStorm.
 * User: dustinleblanc
 * Date: 4/19/16
 * Time: 11:36 PM
 */

namespace Pantheon\Terminus\Command\Auth;


use Pantheon\Terminus\Command\TerminusCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Logout extends TerminusCommand
{
    protected function configure()
    {
        $this->setName('auth:logout');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Logging out of Pantheon.');
        $this->getContainer()->get('FileCache')->remove('session');
    }

}
