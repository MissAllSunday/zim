<?php
// composer autoloader for required packages and dependencies
require_once('lib/autoload.php');
require_once('bootstrap.php');
require_once('routes.php');

/** @var \Base $f3 */
$f3 = \Base::instance();

// Crons
\Cron::instance();

// Init
$f3->run();
