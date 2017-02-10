<?php

namespace Controllers;

class Blog extends Base
{
	function home(\Base $f3, $params)
	{
		$start = $params['page'] ? $params['page'] : 0;
		$entries = $this->_models['message']->entries([
			'limit' => 10,
			'start' => $start,
			'board' => 1
		]);

		$f3->set('messages', $entries);

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
		$start = !empty($params['page']) ? $params['page'] : 0;
		$limit = 10;

		// Ugly, I know...
		$tags = explode('-', $params['title']);

		// The ID will always be the last key. Since we're here, remove it.
		$id = array_pop($tags);

		// Get the entry Info.
		$entryInfo = $this->_models['message']->entryInfo($id);

		$f3->set('entryInfo', $entryInfo);

		// Get the data.
		$f3->set('comments', $this->_models['message']->single([
			':topic' => $id,
			':start' => $start,
			':limit' => $limit,
			':msg' => $entryInfo['msgID']
		]));
var_dump($entryInfo);
		$f3->set('pag', [
			'start' => $start,
			'limit' => $limit,
			'pages' => $entryInfo['pages'],
		]);
		// Build some keywords!  This should be automatically set but meh... maybe later
		$f3->set('site.metaTitle', $entryInfo['title'] . ($start ? $f3->get('txt.page', $start) : ''));
		$f3->set('site.keywords', $f3->get('Tools')->metaKeywords($tags));
		$f3->set('site.description', $f3->get('Tools')->metaDescription($entryInfo['body']), 3600);
		$f3->set('site.breadcrumb', [
			['url' => 'board/'. $entryInfo['boardUrl'], 'title' => $entryInfo['boardTitle']],
			['url' => '', 'title' => $entryInfo['title'] . ($start ? $f3->get('txt.page', $start) : ''), 'active' => true],
		]);

		// Set some vars for the quick Reply option.
		$f3->set('posting',[
			'topicID' => $id,
			'boardID' => $entryInfo['boardID'],
			'title' =>  $f3->get('txt.re'). $entryInfo['title'],
		]);
		$f3->set('quickReply', true);

		$f3->set('content','single.html');
		echo \Template::instance()->render('home.html');
	}
}
