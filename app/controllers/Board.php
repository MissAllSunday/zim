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
		// Another temp thing.
		$id = explode('-', $params['name']);
		$id = array_pop($id);

		$boardInfo = $this->model->load(['boardID = ?', $id]);

		// Get the data.
		$entries = $this->model->topicList([
			'limit' => 10,
			'start' => 0,
			'board' => $boardInfo->boardID,
		]);

		echo \Template::instance()->render('board.html');
	}
}
