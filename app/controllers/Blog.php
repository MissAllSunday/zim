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

	function error(\Base $f3, $params)
	{
		$f3->set('site', $f3->merge('site', [
			'metaTitle' => urldecode($f3->get('ERROR.text')),
			'breadcrumb' => [
				['url' => '', 'title' => $f3->get('ERROR.code'), 'active' => true],
			],
		]));

		if (strpos($f3->get('ERROR.text'), '_spoiler') !== false)
		{
			$like = str_replace([strrchr($f3->get('ERROR.text'), 'spoiler'), 'HTTP 404 (GET'], ['', ''], $f3->get('ERROR.text'));

			if (strpos($like, '_spoiler') !== false)
				$like = str_replace(strrchr($like, 'spoiler'), '', $like);

			$like = strrchr($like, '/');
			$like = trim(str_replace([' ', 'spoiler', '/', '_', 'one', 'piece'], ['', '[Spoiler]','', '', 'One', 'Piece'], $like));
		}

		else
		{
			$char = strpos($f3->get('ERROR.text'), '_') !== false ? '_' : '-';
			$like =
			explode(' ', preg_replace(
							'/[^\00-\255]+/u', '', str_replace(
				['/', $char], ['',' '],
							strrchr(
								str_replace(
									[strrchr(
										$f3->get('ERROR.text'), $char
									), 'one', 'piece', 'spoiler','manga', 'streams'], ['', 'One','Piece', 'Spoiler', '', ''],
										urldecode(
											$f3->get('ERROR.text')
										)
								), '/'
							)
				)
			));

			if (in_array('Spoiler', $like))
			{
				array_shift($like);
				array_pop($like);
			}

			$like = implode('', $like);
		}

		// Try and show a list of similar topics.
		if ($f3->get('ERROR.code') == 404 && !empty($like))
		{
			$s = $this->_models['message']->find(['replace(title, " ", "") LIKE ?', '%'. $like .'%'], [
				'order' => 'msgID DESC',
				'limit' => 10,
			]);

			$f3->set('similarTopics', $s);
		}

		echo \Template::instance()->render('error.html');
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

		if (empty($entryInfo))
			$f3->error(404);

		$f3->set('entryInfo', $entryInfo);

		// Get the data.
		$f3->set('comments', $this->_models['message']->single([
			':topic' => $id,
			':start' => $start,
			':limit' => $limit,
			':msg' => $entryInfo['msgID']
		]));

		$f3->set('pag', [
			'start' => $start,
			'limit' => $limit,
			'pages' => $entryInfo['pages'],
		]);

		// Build some keywords!  This should be automatically set but meh... maybe later
		$f3->set('site', $f3->merge('site', [
			'metaTitle' => $entryInfo['title'] . ($start ? $f3->get('txt.page', $start) : ''),
			'description' => $entryInfo['desc'],
			'keywords' => $f3->get('Tools')->metaKeywords($tags),
			'currentUrl' => $f3->get('BASE') .'/'. $entryInfo['url'],
			'breadcrumb' => [
				['url' => 'board/'. $entryInfo['boardUrl'], 'title' => $entryInfo['boardTitle']],
				['url' => '', 'title' => $entryInfo['title'] . ($start ? $f3->get('txt.page', $start) : ''), 'active' => true],
			],
		]));

		// Set some vars for the quick Reply option.
		if ($f3->get('can.post'))
		{
			$f3->set('posting',[
				'topicID' => $id,
				'boardID' => $entryInfo['boardID'],
				'title' =>  $f3->get('txt.re'). $entryInfo['title'],
			]);
			$f3->set('quickReply', true);
			$f3->set('isEditing', false);
			$f3->set('isTopic', false);

			// Registered users get a nice editor to play with
			if ($f3->get('currentUser')->userID)
			{
				$f3->push('site.customJS', 'summernote.min.js');
				$f3->push('site.customJS', 'summernote-image-attributes.js');
				$f3->push('site.customCSS', 'summernote.css');
			}
		}

		$f3->set('content','single.html');
		echo \Template::instance()->render('home.html');
	}
}
