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

		// Don't need no fancy pagination.
		$f3->set('pagination', array(
			'next' => $start + 1,
			'previous' => ($start ? $start - 1 : 0),
		));

		echo \Template::instance()->render('home.html');
	}

	function single(\Base $f3, $params)
	{
		// Values for pagination.
		$start = $params['page'] ? $params['page'] : 0;
		$limit = 10;

		// Ugly, I know...
		$tags = explode('-', $params['title']);

		// Temp, still haven't build the boards table...
		$f3->set('boards',[
			'1' => 'Blog',
			'2' => 'Chit Chat',
			'3' => 'Manga Releases',
			'4' => 'Spoilers',
			'5' => 'Support,'
		]);

		// The ID will always be the last key. Since we're here, remove it.
		$id = array_pop($tags);

		// Get the entry Info.
		$entryInfo = $this->model->entryInfo($id);
		$entryInfo = $entryInfo[0];

		$f3->set('entryInfo', $entryInfo);

		// Build some keywords!  This should be automatically set but meh... maybe later
		$f3->set('site.keywords', $f3->get('Tools')->metaKeywords($tags));
		$f3->set('site.description', $f3->get('Tools')->metaDescription($entryInfo['body']), 3600);

		// Get the data.
		$single = $this->model->paginate($start, $limit, array('topicID = ?', $id));

		// The main message.
		if (!$start)
			$f3->set('entry', array_shift($single['subset']));

		$f3->set('comments', $single['subset']);

		// Yeah.. I'm lazy so...
		unset($single['subset']);

		// Pass the rest of the info.
		$f3->set('pag', $single);

		echo \Template::instance()->render('entry.html');
	}
}
