<?php

namespace Models;

class Blog extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, 'suki_c_message');
	}

	function topicList($params = [])
	{
		return $this->db->exec('
			SELECT t.topicID, mf.title, mf.url, mf.tags, mf.msgTime, ml.title, ml.url, ml.tags, ml.msgTime
			FROM suki_c_topic AS t
			LEFT JOIN suki_c_message AS mf ON (mf.msgID = t.fmsgID)
			LEFT JOIN suki_c_message AS ml ON (ml.msgID = t.lmsgID)
			WHERE boardID = :board
			ORDER BY t.topicID DESC
			LIMIT :start, :limit', [
			':limit' => $params['limit'],
			':start' => ($params['start'] * $params['limit']),
			':board' => $params['board'],
		]);
	}
}
