<?php

// composer autoloader for required packages and dependencies
require_once('lib/autoload.php');

/** @var \Base $f3 */
$f3 = \Base::instance();
$f3->config('setup.ini');
$f3->set('AUTOLOAD', 'app/');

$f3->set('Config', new \Services\Config($f3));

$f3->get('Config')->init();

// Crons.
\Cron::instance();

$f3->run();
