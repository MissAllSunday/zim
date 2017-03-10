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

	function countTopics($id)
	{
		$data = $this->db->exec('
			SELECT t.topicID
			FROM '. $this->table() .' AS t
			WHERE t.boardID = :board', [
			':board' => $id,
		], 300);

		return count($data);
	}

	function topicList($params = [])
	{
		$f3 = \Base::instance();
		$tags = [];

		$topics = [];
		$r = $this->db->exec('
			SELECT t.topicID, t.locked, t.sticky, mf.title, mf.url, mf.tags, mf.msgTime, ml.msgID AS last_msg, ml.title AS last_title, ml.url AS last_url, ml.msgTime AS last_msgTime, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, mf.userName) AS userName, IFNULL(u.avatar, "") AS avatar, IFNULL(ul.userID, 0) AS last_userID, IFNULL(ul.userName, ml.userName) AS last_userName, IFNULL(ul.avatar, "") AS last_avatar, (SELECT COUNT(*)
				FROM suki_c_message
				WHERE topicID  = t.topicID) as max_count
			FROM '. $this->table() .' AS t
			LEFT JOIN suki_c_message AS mf ON (mf.msgID = t.fmsgID)
			LEFT JOIN suki_c_message AS ml ON (ml.msgID = t.lmsgID)
			LEFT JOIN suki_c_user AS u ON (mf.userID = u.userID)
			LEFT JOIN suki_c_user AS ul ON (ml.userID = ul.userID)
			WHERE mf.boardID = :board
			ORDER BY last_msgTime DESC
			LIMIT :start, :limit', $params, 300);

		// TopicID as key.
		foreach ($r as $v)
		{
			// Tags
			$v['tags'] = empty($v['tags']) ? [] : array_filter(array_unique(explode(',', $v['tags'])));
			$tags = array_merge($tags, $v['tags']);

			$topics[$v['topicID']] = $f3->get('Tools')->prepareData($v);
		}

		$f3->set('tags', array_filter(array_unique($tags)));

		return $topics;
	}
}
