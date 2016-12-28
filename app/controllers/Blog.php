<?php

namespace Controllers;

use Services;

class Blog
{
	function __construct()
	{
		$this->model = new \Models\Blog();
	}

	function home(\Base $f3, $params)
	{
		$start = $params['page'] ? $params['page'] : 0;

		$f3->set('messages', $this->model->getEntries([
			'limit' => 10,
			'start' => $start,
			'board' => 1
		]));

		$f3->set('pagination', array(
			'next' => $start + 1,
			'previous' => ($start ? $start - 1 : 0),
		));

		echo \Template::instance()->render('home.html');
	}

	function single(\Base $f3, $params)
	{
		$single = $this->model->single(array('url=?', $params['blogTitle']));

		$f3->set('single', $sinble);
		$f3->set('comments', $this->model->getComments($entry['entry']->topicID));

		echo \Template::instance()->render('entry.html');
	}
}
