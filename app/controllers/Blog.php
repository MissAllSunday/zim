<?php

namespace Controllers;

use Services;

class Blog
{
	function __construct()
	{
		$this->msgs = new \DB\SQL\Mapper(\Base::instance()->get('DB'),'messages');
	}

	function home(\Base $f3, $params)
	{
		$limit = 10;
		$start = $params['page'] ? $params['page'] : 0;

		$f3->set('messages', $this->msgs->find(array('draft = ?', 0),
			array(
				'order' => 'msgID DESC',
				'limit' => $limit,
				'offset' => $start * $limit,
		)));

		$f3->set('pagination', array(
			'next' => $start + 1,
			'previous' => ($start ? $start - 1 : 0),
		));

		echo \Template::instance()->render('home.html');
	}

	function single(\Base $f3, $params)
	{
		$entry = $this->msgs->load(array('url=?', $params['blogTitle']));
		$f3->get('parser')->addCodeDefinitionSet(new \Services\CustomCodeDefinitionSet());
		$f3->get('parser')->parse($entry['body']);
		$entry['body'] = $f3->get('parser')->getAsHtml();

		$f3->set('entry', $entry);

		echo \Template::instance()->render('entry.html');
	}
}