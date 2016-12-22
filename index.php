<?php
// composer autoloader for required packages and dependencies
require_once('lib/autoload.php');

/** @var \Base $f3 */
$f3 = \Base::instance();

$f3->set('AUTOLOAD', 'app/');
$f3->set('UI', 'app/views/');
$f3->set('SCHEME', 'https');
$f3->set('TZ', 'America/Mexico_City');
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
