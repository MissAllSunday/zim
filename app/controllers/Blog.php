<?php

namespace Controllers;

class Blog extends Base
{
	function home(\Base $f3, $params)
	{
		$start = $params['page'] ? $params['page'] : 0;

		$f3->set('messages', $this->_models['message']->entries([
			'limit' => 10,
			'start' => $start,
			'board' => 1
		]));

		// Don't need no fancy pagination.
		$f3->set('pagination', array(
			'next' => $start + 1,
			'previous' => ($start ? $start - 1 : 0),
		));

		$f3->set('content','blog.html');
		echo \Template::instance()->render('home.html');
	}

	function single(\Base $f3, $params)
	{
		// Values for pagination.
		$start = $params['page'] ? $params['page'] : 0;
		$limit = 10;

		// Ugly, I know...
		$tags = explode('-', $params['title']);

		// The ID will always be the last key. Since we're here, remove it.
		$id = array_pop($tags);

		// Get the entry Info.
		$entryInfo = $this->_models['message']->entryInfo($id);

		$f3->set('entryInfo', $entryInfo);

		// Get the data.
		$single = $this->_models['message']->paginate($start, $limit, array('topicID = ?', $id));

		$f3->set('entry', $entryInfo);

		// The main message.
		if (!$start)
			array_shift($single['subset']);

		$f3->set('comments', $single['subset']);

		// Yeah.. I'm lazy so...
		unset($single['subset']);

		// Pass the rest of the info.
		$f3->set('pag', $single);

		// Build some keywords!  This should be automatically set but meh... maybe later
		$f3->set('site.metaTitle', $entryInfo['title'] . ($start ? $f3->get('txt.page', $single['pos']) : ''));
		$f3->set('site.keywords', $f3->get('Tools')->metaKeywords($tags));
		$f3->set('site.description', $f3->get('Tools')->metaDescription($entryInfo['body']), 3600);
		$f3->set('site.breadcrumb', [
			['url' => 'board/'. $entryInfo['boardUrl'], 'title' => $entryInfo['boardTitle']],
			['url' => '', 'title' => $entryInfo['title'] . ($start ? $f3->get('txt.page', $single['pos']) : ''), 'active' => true],
		]);

		$f3->set('content','single.html');
		echo \Template::instance()->render('home.html');
	}
}
