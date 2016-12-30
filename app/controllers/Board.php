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
		$boards = [
			'Blog' => 1,
			'Chit Chat' => 2,
			'Manga Releases' => 3,
			'Spoilers' => 4,
			'Support' => 5,
		];

		// Safe check
		if (empty($params['name']) || !isset($boards[$params['name']]))
			return;

		echo \Template::instance()->render('board.html');
	}
}
