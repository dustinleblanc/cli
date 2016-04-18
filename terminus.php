#! /usr/bin/env php
<?php

use Pantheon\Terminus\Application;
use Pantheon\Terminus\Config\Services\TerminusServiceProvider;
use Pantheon\Terminus\Container\TerminusContainer;

require __DIR__ . '/vendor/autoload.php';
$serviceProvider = new TerminusServiceProvider();
$container = new TerminusContainer();
$container->addServiceProvider($serviceProvider);

$app = new Application('Terminus', 'Yolo', __DIR__);
$app->setContainer($container);

$app->addCommands($app->getAllCommands());
$app->run();
