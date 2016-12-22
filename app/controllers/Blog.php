<?php

namespace Controllers;

class Blog
{
	function __construct()
	{
		$this->msgs = new \DB\SQL\Mapper(\Base::instance()->get('DB'),'messages');
	}

	function home(\Base $f3, $params)
	{
		$limit = 10;
		$start = $params['page'] ? $params['page'] : 0;

		$f3->set('messages', $f3->get('DB')->exec('SELECT msgTime, title FROM messages ORDER BY msgID DESC LIMIT :start, :limit', array(
			':limit' => $limit,
			':start' => ($start * $limit)
		)));

		$f3->set('pagination', array(
			'next' => $start + 1,
			'previous' => ($start ? $start - 1 : 0),
		));

		echo \Template::instance()->render('home.html');
	}
}