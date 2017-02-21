<?php

// composer autoloader for required packages and dependencies
require_once('lib/autoload.php');

/** @var \Base $f3 */
$f3 = \Base::instance();
$f3->config('setup.ini');
$f3->set('AUTOLOAD', 'app/');

$f3->set('Config', new \Services\Config($f3));

$f3->get('Config')->init();

$f3->set('USER', new \DB\SQL\Session($f3->get('DB'), 'user_ses'));

if (!$f3->exists('SESSION.csrf'))
	$f3->set('SESSION.csrf', $f3->get('USER')->csrf());

// Home and pagination.
$f3->route([
	'GET /',
	'GET /page/@page',
],'\Controllers\Blog->home');

// Single page.
$f3->route([
	'GET /@title',
	'GET /@title/page/@page',
],'\Controllers\Blog->single');

// A single board.
$f3->route([
	'GET /board/@name',
	'GET /board/@name/page/@page',
],'\Controllers\Board->home');

// Posting/Editing page.
$f3->route([
	'GET /post/@boardID/@topicID [sync]',
	'GET /edit/@boardID/@topicID/@msgID [sync]',
	'GET /post/@boardID [sync]',
],'\Controllers\Post->post');

// Posting.
$f3->route([
	'POST /post/@type',
	'POST /edit/@type',
],'\Controllers\Post->create');

// Preview.
$f3->route([
	'GET /post/@boardID/@topicID [ajax]',
	'GET /post/@boardID [ajax]',
],'\Controllers\Post->preview');

// Delete topic.
$f3->route([
	'GET /deleteTopic/@boardID/@topicID',
],'\Controllers\Post->deleteTopic');

// Delete message.
$f3->route([
	'GET /delete/@boardID/@topicID/@msgID',
],'\Controllers\Post->delete');

// Forum.
$f3->route([
	'GET /forum/',
	'GET /forum/page/@page',
],'\Controllers\Forum->home');

// Sign up page
$f3->route([
	'GET /signup',
],'\Controllers\UserAuth->signupPage');

// Sign up page
$f3->route([
	'POST /signup',
],'\Controllers\UserAuth->doSignup');

// Login
$f3->route([
	'POST /login',
],'\Controllers\UserAuth->doLogin');

// Logout
$f3->route([
	'GET /logout',
],'\Controllers\UserAuth->doLogout');

// Full login page
$f3->route([
	'GET /login',
],'\Controllers\UserAuth->loginPage');

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

// Generic identicon
$f3->route('GET /identicon/@str',
	function($f3, $args)
	{
		$img = new \Image();
		$img->identicon(\Web::instance()->slug($f3->clean($args['str'])));
		$img->render('jpeg',NULL,90);
		unset($img);
	},
	3600*24
);

// Background
$f3->route('GET /background',
	function($f3, $args)
	{
		$file = $f3->get('UI') .'img/'. rand(1, 24) .'.jpg';

		header('Content-Type: image/jpeg');
		echo $f3->read($file);
	}, 432000);

// Me!
$f3->route('GET /me',
	function($f3, $args)
	{
		$day = date('j');$month = date('F');
		$path = $f3->get('_rootPath') .'suki/';
		$image = $day <= 15 ? '1' : '2';
		$file = '';

		// Independence Day
		if (($day == 14 || $day == 15 || $day == 16) && $month == 'September')
			$file = 'mexico.gif';

		// Marceline!
		if (in_array($day, array(27, 28, 29, 30, 31)) && $month == 'October')
			$file = 'marceline.gif';

		// Día de muertos
		else if (($day == 1 || $day == 2) && $month == 'November')
			$file = 'catrina.jpg';

		else
			$file =  $month . '-0'. $image . '.gif';

		header('Content-Type: image/gif');
		echo $f3->read($path . $file);
	}, 86400);

$f3->route('GET /testing', function($f3, $args){
	$sp = new \SimplePie;
	$sp->set_output_encoding($f3->get('ENCODING'));
	$sp->enable_cache(false);
	$sp->strip_htmltags(false);
	$sp->set_feed_url('https://github.com/MissAllSunday.atom');
	$sp->init();
echo '<pre>';



list($h, $d) = explode('|', $sp->get_items()[1]->get_id() .'|'. $sp->get_items()[1]->get_date('U'));

echo $h . PHP_EOL;
echo $d . PHP_EOL;
});

// Crons.
\Cron::instance();

$f3->run();
