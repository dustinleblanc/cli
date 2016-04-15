#! /usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Pantheon\Terminus\Application;

$terminus = new Application('Terminus', 'yolo', __DIR__);
$terminus->add(new Pantheon\Terminus\Command\ArtCommand());
$terminus->run();

