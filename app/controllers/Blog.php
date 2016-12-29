<?php

namespace Controllers;

use Services;

class Blog
{
	function __construct()
	{
		$this->model = new \Models\Blog(\Base::instance()->get('DB'));
	}

	function home(\Base $f3, $params)
	{
		$start = $params['page'] ? $params['page'] : 0;

		$f3->set('messages', $this->model->entries([
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
		// Ugly, I know...
		$tags = explode('-', $params['title']);

		// The ID will always be the last key. Since we're here, remove it.
		$id = array_pop($tags);

		$single = $this->model->single(array('topicID = ?', $id));

		echo \Template::instance()->render('entry.html');
	}
}
