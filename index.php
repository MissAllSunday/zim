<?php
// composer autoloader for required packages and dependencies
require_once('lib/autoload.php');

/** @var \Base $f3 */
$f3 = \Base::instance();

$f3->mset(array(
	'AUTOLOAD' => 'app/',
	'UI' => 'app/views/',
	'SCHEME' => 'https',
	'TZ' => 'America/Mexico_City',
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

$f3->run();
