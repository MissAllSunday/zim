<?php

namespace Models;

class Board extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
				parent::__construct($db, \Base::instance()->get('_db.prefix') . 'board');
	}

	function getBoards()
	{
		return $this->db->exec('
			SELECT *
			FROM '. $this->table() .'', null, 604800);
	}

	function boardList()
	{
		$f3 = \Base::instance();
		$boards = [];
		$r = $this->db->exec('
			SELECT b.boardID, b.title, b.description, b.url, b.icon, m.msgID, m.title AS msgTitle, m.url AS msgUrl, m.msgTime, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar,
			(SELECT COUNT(*)
				FROM suki_c_message
				WHERE topicID  = m.topicID) as max_count,
			(SELECT COUNT(*)
				FROM suki_c_topic
				WHERE boardID  = b.boardID) as totalTopics,
			(SELECT COUNT(*)
				FROM suki_c_message
				WHERE boardID  = b.boardID) as totalPosts
			FROM '. $this->table() .' AS b
				LEFT JOIN suki_c_message AS m ON (m.msgID = (SELECT msgID
					FROM suki_c_message
					WHERE boardID  = b.boardID
					ORDER BY msgTime DESC
					LIMIT 1))
				LEFT JOIN suki_c_user AS u ON (m.userID = u.userID)', null, 3600);

		foreach ($r as $b)
		{
			$b['msgDate'] = $f3->get('Tools')->getDate($b['msgTime']);

			if (empty($b['avatar']))
				$b['avatar'] = $f3->get('BASE') .'/identicon/'. $b['userName'];

			$b['date'] = $f3->get('Tools')->getDate($b['msgTime']);

			if ($b['max_count'] > $f3->get('paginationLimit'))
				$b['msgUrl'] = $b['msgUrl'] . '/page/' . (int) ($b['max_count'] / $f3->get('paginationLimit')) . '#msg'. $b['msgID'];

			else
				$b['msgUrl'] = $b['msgUrl'] . '#msg'. $b['msgID'];

			$boards[$b['boardID']] = $b;
		}

		return $boards;
	}
}
