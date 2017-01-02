<?php

// composer autoloader for required packages and dependencies
require_once('lib/autoload.php');

/** @var \Base $f3 */
$f3 = \Base::instance();

$f3->set('AUTOLOAD', 'app/');

$f3->set('Config', new \Services\Config($f3));

$f3->get('Config')->init();

// Home and pagination.
$f3->route(array(
	'GET /',
	'GET /page/@page',
),'\Controllers\Blog->home');

// Single page.
$f3->route(array(
	'GET /@title',
	'GET /@title/page/@page',
),'\Controllers\Blog->single');

// Forum.
$f3->route(array(
	'GET /forum/',
	'GET /forum/page/@page',
),'\Controllers\Forum->home');

// A single board.
$f3->route(array(
	'GET /board/@name',
	'GET /board/@name/page/@page',
),'\Controllers\Board->home');

// Login
$f3->route(array(
	'POST /login',
),'\Controllers\User->doLogin');

// Login
$f3->route(array(
	'GET /logout',
),'\Controllers\User->doLogout');

// Full login page
$f3->route(array(
	'GET /login',
),'\Controllers\User->loginPage');

// JS and Css minification.
$f3->route('GET /minify/@type',
	function($f3, $args)
	{
		$path = $f3->get('UI') . $args['type'] .'/';
		$files = preg_replace('/(\.+\/)/','', $f3->clean($f3->get('GET.files')));
		echo Web::instance()->minify($files, null, true, $path);
	},
	3600*24
);

$f3->run();
