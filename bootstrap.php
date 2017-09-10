<?php

/** @var \Base $f3 */
$f3 = \Base::instance();
$f3->config('setup.ini');
$f3->set('AUTOLOAD', 'app/');
$f3->set('Config', new \Services\Config($f3));
$f3->get('Config')->init();
$f3->set('USER', new \DB\SQL\Session($f3->get('DB'), $f3->get('_db.prefix') . 'ses', true, function($session){
		return true;
	}));

if (!$f3->exists('SESSION.csrf'))
	$f3->set('SESSION.csrf', $f3->get('USER')->csrf());
