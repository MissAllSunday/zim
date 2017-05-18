<?php

namespace Controllers;

class Blog extends Base
{
	function home(\Base $f3, $params)
	{
		$start = $params['page'] ?: 0;
		$entries = $this->_models['message']->entries([
			':limit' => $f3->get('paginationLimit'),
			':start' => $start * $f3->get('paginationLimit'),
			':board' => 1
		]);

		// Extract the images if there are any.
		$tags = '';
		$images = new \Models\Images;

		foreach($entries as $k => $entry)
		{
			$tags .= $entry['desc'] .' ';

			// Get the image.
			$entries[$k]['image'] = $images->extractImage($entry['body']);
		}

		// Get some moar tags
		$tags = $f3->get('Tools')->generateKeywords($tags);

		$f3->set('messages', $entries);

		// Don't need no fancy pagination.
		$f3->set('pagination', array(
			'next' => $start + 1,
			'previous' => ($start ? $start - 1 : 0),
		));

		$home = $f3->get('txt.home') . ($start ? $f3->get('txt.page', $start) : '');

		$f3->concat('site.metaTitle', $home);
		$f3->set('site.keywords', $tags);

		$f3->set('content','blog.html');
	}

	function single(\Base $f3, $params)
	{
		// Values for pagination.
		$start = !empty($params['page']) ? $params['page'] : 0;
		$limit = $f3->get('paginationLimit');

		// Ugly, I know...
		$tags = explode('-', $params['title']);

		// The ID will always be the last key. Since we're here, remove it.
		$id = array_pop($tags);

		// Get the entry Info.
		$entryInfo = $this->_models['message']->entryInfo($id);

		if (empty($entryInfo))
			return $f3->error(404);

		// Get some moar tags
		$tags = array_unique(array_merge($tags, explode(',', $entryInfo['tags']), explode(',', $f3->get('Tools')->generateKeywords($entryInfo['desc']))));

		$f3->set('entryInfo', $entryInfo);

		// Get the data.
		$comments = $this->_models['message']->comments([
			':topic' => $id,
			':start' => $start * $limit,
			':limit' => $limit,
		]);

		// Don't need the first msg.
		if (isset($comments[$entryInfo['msgID']]))
			unset($comments[$entryInfo['msgID']]);

		if ($start)
		{
			$firstComment = current($comments);
			$f3->set('entryInfo.date', $firstComment['date']);
			$f3->set('entryInfo.microDate', $firstComment['microDate']);
		}

		$f3->set('comments', $comments);

		$f3->set('pag', [
			'start' => $start,
			'limit' => $limit,
			'pages' => $entryInfo['pages'],
			'url' => $entryInfo['pagUrl'],
			'extra' => '#comments',
		]);

		// Build some keywords!  This should be automatically set but meh... maybe later
		$f3->set('site', $f3->merge('site', [
			'metaTitle' => $entryInfo['title'] . ($start ? $f3->get('txt.page', $start) : ''),
			'description' => $entryInfo['desc'] . ($start ? $f3->get('txt.page', $start) : ''),
			'keywords' => $f3->get('Tools')->metaKeywords($tags),
			'currentUrl' => $f3->get('URL') .'/'. $entryInfo['url'],
			'breadcrumb' => [
				['url' => $f3->get('URL') . '/board/'. $entryInfo['boardUrl'], 'title' => $entryInfo['boardTitle']],
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

		// Update the last message saw by the current user.
		$this->_models['user']->lmsgID = $entryInfo['lmsgID'];

		$f3->set('content','single.html');
	}

	function error(\Base $f3, $params)
	{
		$f3->concat('site.metaTitle', urldecode($f3->get('ERROR.text')));
		$f3->set('site', $f3->merge('site', [
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
			$f3->set('similarTopics', $this->_models['message']->find(['replace(title, " ", "") LIKE ?', '%'. $like .'%'], [
				'order' => 'msgID DESC',
				'limit' => 10,
			]));

		echo \Template::instance()->render('error.html');
	}

	function search(\Base $f3, $params)
	{
		$f3->set('site', $f3->merge('site', [
			'metaTitle' => $f3->concat('site.metaTitle', $f3->get('txt.search')),
			'description' => $f3->get('txt.search_desc'),
			'currentUrl' => $f3->get('BASE') .'/about',
			'breadcrumb' => [
				['url' => '', 'title' => $f3->get('txt.search'), 'active' => true],
			],
		]));
		$f3->set('content','search.html');
	}

	function about(\Base $f3, $params)
	{
		$f3->set('site', $f3->merge('site', [
			'metaTitle' => $f3->get('txt.about'),
			'description' => $f3->get('txt.about'),
			'currentUrl' => $f3->get('BASE') .'/about',
			'breadcrumb' => [
				['url' => '', 'title' => $f3->get('txt.about'), 'active' => true],
			],
		]));

		$f3->set('stuff', [
			'https://fatfreeframework.com' => 'Fat-Free Framework',
			'http://getbootstrap.com' => 'Bootstrap',
			'http://fontawesome.io' => 'Font Awesome',
			'https://jquery.com' => 'jQuery',
			'http://summernote.org' => 'summernote',
			'https://highlightjs.org' => 'highlight.js',
			'http://htmlpurifier.org' => 'HTML Purifier',
			'http://simplepie.org' => 'SimplePie',
			'https://github.com/KnpLabs/php-github-api' => 'PHP GitHub API 2.0',
			'https://startbootstrap.com' => 'Start Bootstrap Clean Blog template',
			'https://github.com/davidmerfield/randomColor/' => 'randomColor',
			'https://github.com/filamentgroup/loadCSS' => 'loadCSS',
		]);

		$f3->set('content','about.html');
	}
}
