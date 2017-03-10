<?php

namespace Models;

class Topic extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, \Base::instance()->get('_db.prefix') . 'topic');
	}

	function getByUser($params = [], $ttl = 0)
	{
		$f3 = \Base::instance();
		$data = [];

		$data = $this->db->exec('
			SELECT t.locked, t.sticky, t.lmsgID, m.msgID, m.topicID, m.msgTime, m.title, m.tags, m.url, m.boardID, b.title AS boardTitle, b.url AS boardUrl, m.userEmail, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar, (u.last_active >= UNIX_TIMESTAMP() - 300) AS isOnline
			FROM '. $this->table() .' AS t
			LEFT JOIN suki_c_message AS m ON (m.msgID = t.fmsgID)
			LEFT JOIN suki_c_board AS b ON (b.boardID = t.boardID)
			LEFT JOIN suki_c_user AS u ON (u.userID = m.userID)
			WHERE m.userID = :user
			ORDER BY t.topicID DESC
			LIMIT :limit', $params, $ttl);

		foreach ($data as $k => $v)
			$data[$k] = $f3->get('Tools')->prepareData($v);

		return $data;
	}
}
