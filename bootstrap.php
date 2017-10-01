<?php

/** @var \Base $f3 */
$f3 = \Base::instance();
$f3->config('setup.ini');
$f3->set('AUTOLOAD', 'app/');
$f3->set('DB', new \DB\SQL(
	$f3->get('_db.type') .':host='. $f3->get('_db.host') .';port='. $f3->get('_db.port') .';dbname='. $f3->get('_db.name') .'',
	$f3->get('_db.user'),
	$f3->get('_db.password'),
	[\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;']
));

$f3->set('USER', new \DB\SQL\Session($f3->get('DB'), $f3->get('_db.prefix') . 'ses', true, function($session){
		return true;
	}));

if (!$f3->exists('SESSION.csrf'))
	$f3->set('SESSION.csrf', $f3->get('USER')->csrf());
