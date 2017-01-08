<?php

namespace Services;

class Config
{
	function __construct(\Base $f3)
	{
		$this->f3 = $f3;
	}

	function init()
	{
		$this->f3->config('setup.ini');
		$this->f3->set('DB', new \DB\SQL(
			$this->f3->get('_db.type') .':host='. $this->f3->get('_db.host') .';port='. $this->f3->get('_db.port') .';dbname='. $this->f3->get('_db.name') .'',
			$this->f3->get('_db.user'),
			$this->f3->get('_db.password'),
			[\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4;']
		));

		$this->f3->mset([
			'CACHE' => true,
			'DEBUG' => 3,
			'PREFIX' => 'txt.',
			'ENCODING' => 'UTF-8',
			'LOCALES' => 'dict/',
			'LANGUAGE' => 'en-US',
			'UI' => 'views/',
			'SCHEME' => 'http',
			'TZ' => 'America/Mexico_City',
			'site' => array(
				'title' => 'Miss All Sunday'
			),
		]);

		// This should be automatically set.... @todo
		$this->f3->set('Tools', new \Services\Tools($this->f3));

		// Set default metadata tags and/or other common HTML tags.
		$this->f3->set('site.keywords', $this->f3->get('txt.site_keywords'));
		$this->f3->set('site.description', $this->f3->get('txt.site_desc'));

		// Declare these as an empty array.
		$this->f3->set('site.customJS', []);
		$this->f3->set('site.customCSS', []);

		// If theres an cookie, set the session.
		$c = md5($this->f3->get('site.title'));

		if ($this->f3->exists('COOKIE.'. $c))
			$this->f3->set('SESSION.user', $this->f3->exists('COOKIE.'. $c));
	}
}
