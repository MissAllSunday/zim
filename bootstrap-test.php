<?php

define('ROOT', __DIR__);

// composer autoloader
require_once('lib/autoload.php');

$f3 = \Base::instance();
$f3->config('setup.ini');
$f3->set('AUTOLOAD', 'app/');
$f3->set('DB', new \DB\SQL(
	$f3->get('_db.type') .':host='. $f3->get('_db.host') .';port='. $f3->get('_db.port') .';dbname='. $f3->get('_db.name') .'',
	"root",
	"",
	[\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;']
));
$f3->set('USER', new \DB\SQL\Session($f3->get('DB'), $f3->get('_db.prefix') . 'ses', true, function($session){
		return true;
	}));
$f3->set('QUIET', true);
$f3->set('ONERROR',function(){});

require_once('routes.php');
