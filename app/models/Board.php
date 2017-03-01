<?php

namespace Models;

class Board extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, 'suki_c_board');
	}

	function getBoards()
	{
		return $this->db->exec('
			SELECT *
			FROM suki_c_board', null, 604800);
	}

	function countTopics($id)
	{
		$data = $this->db->exec('
			SELECT t.topicID
			FROM suki_c_topic AS t
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
			$v['tags'] = empty($v['tags']) ? [] : array_filter(array_unique(explode(',', $v['tags'])));
			$tags = array_merge($tags, $v['tags']);

			// Date
			$v['date'] = $f3->get('Tools')->getDate($v['msgTime']);
			$v['last_date'] =  $f3->get('Tools')->getDate($v['last_msgTime']);

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

		$f3->set('tags', array_filter(array_unique($tags)));
		unset($r);
		return $topics;
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
			FROM suki_c_board AS b
				LEFT JOIN suki_c_message AS m ON (m.msgID = (SELECT msgID
					FROM suki_c_message
					WHERE boardID  = b.boardID
					ORDER BY msgTime DESC
					LIMIT 1))
				LEFT JOIN suki_c_user AS u ON (m.userID = u.userID)', null, 3600);

		foreach ($r as $b)
		{
			if (empty($b['avatar']))
				$b['avatar'] = $f3->get('BASE') .'/identicon/'. $b['userName'];

			$b['date'] = $f3->get('Tools')->getDate($b['msgTime']);

			if ($b['max_count'] > $f3->get('paginationLimit'))
				$b['msgUrl'] = $b['msgUrl'] . '/page/' . (int) ($b['max_count'] / $f3->get('paginationLimit')) . '#msg'. $b['msgID'];

			$boards[$b['boardID']] = $b;
		}

		return $boards;
	}
}
