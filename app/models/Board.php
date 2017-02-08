<?php

namespace Models;

class Board extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, 'suki_c_board');
	}

	function topicList($params = [])
	{
		$topics = [];
		$r = $this->db->exec('
			SELECT t.topicID, mf.title, mf.url, mf.tags, mf.msgTime, ml.msgID AS last_msg, ml.title AS last_title, ml.url AS last_url, ml.tags AS last_tags, ml.msgTime AS last_msgTime, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, mf.userName) AS userName, IFNULL(u.avatar, "") AS avatar, IFNULL(ul.userID, 0) AS luserID, IFNULL(ul.userName, ml.userName) AS luserName, IFNULL(ul.avatar, "") AS lavatar, (SELECT COUNT(*)
				FROM suki_c_message
				WHERE topicID  = t.topicID) as max_count
			FROM suki_c_topic AS t
			LEFT JOIN suki_c_message AS mf ON (mf.msgID = t.fmsgID)
			LEFT JOIN suki_c_message AS ml ON (ml.msgID = t.lmsgID)
			LEFT JOIN suki_c_user AS u ON (mf.userID = u.userID)
			LEFT JOIN suki_c_user AS ul ON (ml.userID = ul.userID)
			WHERE mf.boardID = :board
			ORDER BY last_msgTime DESC
			LIMIT :start, :limit', [
			':limit' => $params['limit'],
			':start' => ($params['start'] * $params['limit']),
			':board' => $params['board'],
		]);

		// TopicID as key.
		foreach ($r as $v)
		{
			// Build the last msg url if needed.
			if ($v['max_count'] > $params['limit'])
				$v['last_url'] = $v['last_url'] . '/page/' . (int) ($v['max_count'] / ($params['limit']));

			// No? then just build an anchor tag.
			else
				$v['last_url'] = $v['last_url'] . '#msg'. $v['last_msg'];

			// Work with tags.

			$topics[$v['topicID']] = $v;
		}

		unset($r);
		return $topics;
	}
}
