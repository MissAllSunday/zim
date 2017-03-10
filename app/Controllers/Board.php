<?php

namespace Controllers;


class Board extends Base
{
	function __construct()
	{
		// Need some extra stuff.
		$this->_defaultModels[] = 'topic';
	}

	function home(\Base $f3, $params)
	{
		$start = !empty($params['page']) ? $params['page'] : 0;
		$limit = $f3->get('paginationLimit');

		// Get the ID.
		$tags = explode('-', $params['name']);
		$id = array_pop($tags);

		$f3->set('boardInfo', $this->_models['board']->load(['boardID = ?', $id]));

		$entries = $this->_models['board']->topicList([
			':limit' => $limit,
			':start' => $start * $limit,
			':board' => $f3->get('boardInfo')->boardID,
		]);

		// Sticky first!
		\Matrix::instance()->sort($entries, 'sticky', SORT_DESC);

		// Get the data.
		$f3->set('entries', $entries);

		$f3->set('pag', [
			'start' => $start,
			'limit' => $limit,
			'pages' => ceil($this->_models['board']->countTopics($id) / $limit),
		]);

		$f3->set('site', $f3->merge('site', [
			'metaTitle' => $f3->get('boardInfo')->title,
			'description' => $f3->get('Tools')->metaDescription($f3->get('boardInfo')->description),
			'keywords' => $f3->get('Tools')->metaKeywords(array_merge($tags, $f3->get('tags'))),
			'currentUrl' => $f3->get('boardInfo')->url,
			'breadcrumb' => [
				['url' => 'board/'. $f3->get('boardInfo')->url, 'title' => $f3->get('boardInfo')->title],
			],
		]));

		// Pretty tags!
		$f3->push('site.customJS', 'randomColor.js');

		$f3->set('content','topics.html');
	}

	function forum(\Base $f3, $params)
	{
		// Get the boards.
		$f3->set('boardList', $this->_models['board']->boardList());

		$f3->set('site', $f3->merge('site', [
			'metaTitle' => $f3->get('txt.boards'),
			'currentUrl' => $f3->get('boardInfo')->url,
			'breadcrumb' => [
				['url' => 'forum', 'title' => $f3->get('txt.boards')],
			],
		]));

		$f3->set('content','boards.html');
	}
}
