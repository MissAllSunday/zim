<?php

define('ROOT', __DIR__);

// composer autoloader
require_once('lib/autoload.php');

$f3 = \Base::instance();
$f3->set('AUTOLOAD', 'app/');
$f3->set('DB', new \DB\SQL(
	"mysql:host=localhost;port="";dbname=test_zim",
	"root",
	"",
	[\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;']
));
$f3->set('USER', []);
$f3->set('QUIET', true);
$f3->set('ONERROR',function(){});

require_once('routes.php');
