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

		$entries = $this->_models['topic']->topicList([
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
			'pages' => ceil($this->_models['topic']->countTopics($id) / $limit),
			'url' => 'board/'. $f3->get('boardInfo')->url,
		]);

		$f3->set('site', $f3->merge('site', [
			'keywords' => $f3->get('Tools')->truncateString($f3->get('Tools')->metaKeywords(array_merge($tags, $f3->get('tags'))), 140, ',', ''),
			'currentUrl' => $f3->get('URL') .'/board/'. $f3->get('boardInfo')->url . ($start ? '/page/'. $start : ''),
		]));

		$f3->concat('site.metaTitle', $f3->get('boardInfo')->title . ($start ? $f3->get('txt.page', $start) : ''));

		$f3->set('site.description', $f3->get('site.metaTitle') . ' '. $f3->get('Tools')->metaDescription($f3->get('boardInfo')->description));
		$f3->set('site.breadcrumb', [
			['url' => $f3->get('site.currentUrl'), 'title' => $f3->get('boardInfo')->title . ($start ? $f3->get('txt.page', $start) : '')],
		]);

		// Pretty tags!
		$f3->push('site.customJS', 'randomColor.js');

		$f3->set('content','topics.html');
	}

	function forum(\Base $f3, $params)
	{
		// Get the boards.
		$f3->set('boardList', $this->_models['board']->boardList());

		$f3->set('site', $f3->merge('site', [
			'currentUrl' => $f3->get('boardInfo')->url,
			'breadcrumb' => [
				['url' => 'forum', 'title' => $f3->get('txt.boards')],
			],
		]));

		$f3->concat('site.metaTitle', $f3->get('txt.boards'));

		$f3->set('content','boards.html');
	}
}
