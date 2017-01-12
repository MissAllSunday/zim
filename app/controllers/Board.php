<?php

namespace Controllers;


class Board extends Base
{
	function __construct()
	{
		// Need some extra stuff.
		$this->_defaultModels[] = 'board';
	}

	function home(\Base $f3, $params)
	{
		// Get the ID.
		$tags = explode('-', $params['name']);
		$id = array_pop($tags);

		$f3->set('boardInfo', $this->_models['board']->load(['boardID = ?', $id]));

		$f3->set('site.metaTitle', $f3->get('boardInfo')->title);
		$f3->set('site.keywords', $f3->get('Tools')->metaKeywords($tags));
		$f3->set('site.description', $f3->get('Tools')->metaDescription($f3->get('boardInfo')->description), 3600);

		// Get the data.
		$f3->set('entries', $this->_models['board']->topicList([
			'limit' => 10,
			'start' => 0,
			'board' => $f3->get('boardInfo')->boardID,
		]));

		$f3->set('content','boards.html');
		echo \Template::instance()->render('home.html');
	}
}
