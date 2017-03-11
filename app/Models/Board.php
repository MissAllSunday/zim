<?php

namespace Models;

class Board extends \DB\SQL\Mapper
{
	private static $_prefix;

	function __construct(\DB\SQL $db)
	{
		self::$_prefix = \Base::instance()->get('_db.prefix');
		parent::__construct($db, self::$_prefix . 'board');
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
				FROM '. self::$_prefix .'message
				WHERE topicID  = m.topicID) as numReplies,
			(SELECT COUNT(*)
				FROM '. self::$_prefix .'topic
				WHERE boardID  = b.boardID) as totalTopics,
			(SELECT COUNT(*)
				FROM '. self::$_prefix .'message
				WHERE boardID  = b.boardID) as totalPosts
			FROM '. $this->table() .' AS b
				LEFT JOIN '. self::$_prefix .'message AS m ON (m.msgID = (SELECT msgID
					FROM '. self::$_prefix .'message
					WHERE boardID  = b.boardID
					ORDER BY msgTime DESC
					LIMIT 1))
				LEFT JOIN '. self::$_prefix .'user AS u ON (m.userID = u.userID)', null, 3600);

		foreach ($r as $b)
			$boards[$b['boardID']] = $f3->get('Tools')->prepareData($b);

		return $boards;
	}
}
