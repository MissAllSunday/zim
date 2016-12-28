<?php

namespace Controllers;

use Services;

class Blog
{
	function __construct()
	{
		$this->msgs = new \DB\SQL\Mapper(\Base::instance()->get('DB'),'suki_c_message');
	}

	function home(\Base $f3, $params)
	{
		$limit = 10;
		$start = $params['page'] ? $params['page'] : 0;

		$f3->set('messages', $f3->get('DB')->exec('
			SELECT m.msgTime, m.title, m.url
			FROM suki_c_topic AS t
			LEFT JOIN suki_c_message AS m ON (m.msgID = t.fmsgID)
			WHERE boardID = 1
			ORDER BY m.msgID DESC
			LIMIT :start, :limit', array(
			':limit' => $limit,
			':start' => ($start * $limit)
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

		$f3->set('entry', $entry);

		echo \Template::instance()->render('entry.html');
	}
}
