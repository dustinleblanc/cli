<?php
/**
 * Created by PhpStorm.
 * User: dustinleblanc
 * Date: 4/19/16
 * Time: 7:26 PM
 */

namespace Pantheon\Terminus\Command\Auth;


use Pantheon\Terminus\Command\TerminusCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WhoAmI extends TerminusCommand
{
    protected function configure()
    {
        $this->setName('auth:whoami');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $output->writeln(
            $this->getContainer()->get('Auth')->getCurrentUser()->serialize()
        );
    }

}
