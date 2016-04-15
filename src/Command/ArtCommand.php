<?php

namespace Pantheon\Terminus\Command;

use cli\Colors;
use cli\Shell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Terminus\Utils;
use Terminus\Exceptions\TerminusException;

/**
 * Print the Pantheon art
 *
 * @command art
 */
class ArtCommand extends TerminusCommand
{

    private $works = array('druplicon', 'fist', 'unicorn', 'wordpress');

    /**
     * Returns a colorized string
     *
     * @param string $string Message to colorize for output
     * @return string
     */
    private function colorize($string, InputInterface $input)
    {
        $colorization_setting = $input->getOption('colorize');
        $colorize = (
          !Shell::isPiped()
          || (is_bool($colorization_setting) && $colorization_setting)
        );
        $colorized_string = Colors::colorize($string, $colorize);
        return $colorized_string;
    }

    protected function configure()
    {
        $this->setName("art")
          ->setDescription('Show jawsome art')
          ->addArgument(
            'work',
            InputArgument::OPTIONAL,
            'What fancy art do you want to look at?'
          )
          ->addOption(
            'colorize',
            null,
            InputOption::VALUE_NONE,
            'If set, output will be colorized'
          );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $work = $input->getArgument('work') ?: $this->works[array_rand($this->works)];
        $artwork_content = file_get_contents($this->getApplication()
            ->getWorkingDir() . "/assets/{$work}.txt");

        try {

            $output->writeln(
              $this->colorize("%g" . base64_decode($artwork_content) . "%n",
                $input)
            );
        } catch (TerminusException $e) {
            $this->failure(
              'There is no source for the requested "{artwork}" artwork.',
              compact('artwork')
            );
        }
    }
}
