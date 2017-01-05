<?php

// composer autoloader for required packages and dependencies
require_once('lib/autoload.php');

/** @var \Base $f3 */
$f3 = \Base::instance();

$f3->set('AUTOLOAD', 'app/');

$f3->set('Config', new \Services\Config($f3));

$f3->get('Config')->init();

$f3->set('USER', new \DB\SQL\Session($f3->get('DB'), 'user_ses'));

if (!$f3->exists('SESSION.csrf'))
	$f3->set('SESSION.csrf', $f3->get('USER')->csrf());

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

// Posting page.
$f3->route(array(
	'GET /post/@boardID/@topicID [sync]',
	'GET /post/@boardID [sync]',
),'\Controllers\Post->post');

// Posting.
$f3->route(array(
	'POST /post',
),'\Controllers\Post->create');

// Preview.
$f3->route(array(
	'GET /post/@boardID/@topicID [ajax]',
	'GET /post/@boardID [ajax]',
),'\Controllers\Post->preview');

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
),'\Controllers\UserAuth->doLogin');

// Logout
$f3->route(array(
	'GET /logout',
),'\Controllers\UserAuth->doLogout');

// Full login page
$f3->route(array(
	'GET /login',
),'\Controllers\UserAuth->loginPage');

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

// Captcha.
$f3->route('GET /captcha',
	function($f3, $args)
	{
		$img = new Image();
		$img->captcha('fonts/Roboto-Regular.ttf',16,5,'SESSION.captcha_code');
		$img->render();
	});


$f3->run();
