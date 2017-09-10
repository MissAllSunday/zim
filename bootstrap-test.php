<?php

define('ROOT', __DIR__);

// composer autoloader
require_once('lib/autoload.php');

$f3 = \Base::instance();
$f3->config('setup.ini');
$f3->config('setup-local.ini');
$f3->set('AUTOLOAD', 'app/');
$f3->set('Config', new \Services\Config($f3));
$f3->get('Config')->init();
$f3->set('USER', new \DB\SQL\Session($f3->get('DB'), $f3->get('_db.prefix') . 'ses', true, function($session){
		return true;
	}));
$f3->set('QUIET', true);
$f3->set('APP.TEST', true);
$f3->set('ONERROR',function(){});

require_once('routes.php');
