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
		$f3 = \Base::instance();
		$tags = '';

		$topics = [];
		$r = $this->db->exec('
			SELECT t.topicID, mf.title, mf.url, mf.tags, mf.msgTime, ml.msgID AS last_msg, ml.title AS last_title, ml.url AS last_url, ml.msgTime AS last_msgTime, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, mf.userName) AS userName, IFNULL(u.avatar, "") AS avatar, IFNULL(ul.userID, 0) AS last_userID, IFNULL(ul.userName, ml.userName) AS last_userName, IFNULL(ul.avatar, "") AS last_avatar, (SELECT COUNT(*)
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
			// Tags
			$tags .= $v['tags'];

			// Date
			$v['date'] = $f3->get('Tools')->realDate($v['msgTime']);
			$v['last_date'] =  $f3->get('Tools')->realDate($v['last_msgTime']);

			// Build the last msg url if needed.
			if ($v['max_count'] > $params['limit'])
				$v['last_url'] = $v['last_url'] . '/page/' . (int) ($v['max_count'] / ($params['limit']));

			// No? then just build an anchor tag.
			else
				$v['last_url'] = $v['last_url'] . '#msg'. $v['last_msg'];

			if (empty($v['avatar']))
				$v['avatar'] = $f3->get('BASE') .'/identicon/'. $v['userName'];

			if (empty($v['last_avatar']))
				$v['last_avatar'] = $f3->get('BASE') .'/identicon/'. $v['last_userName'];

			$topics[$v['topicID']] = $v;
		}

		$f3->set('tags', array_filter(array_unique(!empty($tags) ? explode(',', trim($tags)) : [])));
		unset($r);
		return $topics;
	}
}
