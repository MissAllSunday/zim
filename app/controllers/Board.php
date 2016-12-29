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
		echo \Template::instance()->render('board.html');
	}
}
