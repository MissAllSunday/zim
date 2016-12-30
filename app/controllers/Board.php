<?php

namespace Controllers;


class Board
{
	function __construct()
	{
		$this->model = new \Models\Board(\Base::instance()->get('DB'));
	}

	function home(\Base $f3, $params)
	{
		// Get the ID.
		$tags = explode('-', $params['name']);
		$id = array_pop($tags);

		$f3->set('boardInfo', $this->model->load(['boardID = ?', $id]));

		$f3->set('site.metaTitle', $f3->get('boardInfo')->title);
		$f3->set('site.keywords', $f3->get('Tools')->metaKeywords($tags));
		$f3->set('site.description', $f3->get('Tools')->metaDescription($f3->get('boardInfo')->description), 3600);

		// Get the data.
		$f3->set('entries', $this->model->topicList([
			'limit' => 10,
			'start' => 0,
			'board' => $f3->get('boardInfo')->boardID,
		]));

		echo \Template::instance()->render('board.html');
	}
}
