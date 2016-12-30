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
		return $this->db->exec('
			SELECT t.topicID, mf.title, mf.url, mf.tags, mf.msgTime, ml.title AS last_title, ml.url AS last_url, ml.tags AS last_tags, ml.msgTime AS last_msgTime
			FROM suki_c_topic AS t
			LEFT JOIN suki_c_message AS mf ON (mf.msgID = t.fmsgID)
			LEFT JOIN suki_c_message AS ml ON (ml.msgID = t.lmsgID)
			WHERE mf.boardID = :board
			ORDER BY t.topicID DESC
			LIMIT :start, :limit', [
			':limit' => $params['limit'],
			':start' => ($params['start'] * $params['limit']),
			':board' => $params['board'],
		]);
	}
}
