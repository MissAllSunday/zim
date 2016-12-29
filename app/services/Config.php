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
		$this->f3->config('setup.cfg');
		$this->f3->set('DB', new \DB\SQL(
			$this->f3->get('_db.type') .':host='. $this->f3->get('_db.host') .';port='. $this->f3->get('_db.port') .';dbname='. $this->f3->get('_db.name') .'',
			$this->f3->get('_db.user'),
			$this->f3->get('_db.password')
		));

		$this->f3->mset(array(
			'UI' => 'views/',
			'SCHEME' => 'https',
			'TZ' => 'America/Mexico_City',
			'site' => array(
				'title' => 'Miss All Sunday'
			),
		));

		$this->f3->set('Tools', new \Services\Tools($this->f3));
	}
}
