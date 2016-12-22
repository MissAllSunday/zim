<?php
// composer autoloader for required packages and dependencies
require_once('lib/autoload.php');

/** @var \Base $f3 */
$f3 = \Base::instance();

$f3->mset(array(
	'AUTOLOAD' => 'app/',
	'UI' => 'views/',
	'SCHEME' => 'https',
	'TZ' => 'America/Mexico_City',
	'site' => array(
		'title' => 'Miss All Sunday'
	),
));

$f3->config('setup.cfg');
$f3->set('DB', new DB\SQL(
	''. $f3->get('_db.type') .':host='. $f3->get('_db.host') .';port='. $f3->get('_db.port') .';dbname='. $f3->get('_db.name') .'',
	 $f3->get('_db.user'),
	$f3->get('_db.password')
));

$f3->route(array(
	'GET /',
	'GET /page/@page',
),'\Controllers\Blog->home');

$f3->route('GET /minify/@type',
	function($f3, $args)
	{
		$path = $f3->get('UI') . $args['type'].'/';
		$files = preg_replace('/(\.+\/)/','', $f3->clean($f3->get('GET.files')));
		echo Web::instance()->minify($files, null, true, $path);
	},
	3600*24
);

$f3->run();
