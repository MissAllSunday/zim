<?php

namespace Models;

class Blog extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, 'suki_c_message');
	}

	function single($params = array())
	{
		return $this->load($params);
	}

	function entries($params = array())
	{
		return $this->db->exec('
			SELECT m.msgTime, m.title, m.url
			FROM suki_c_topic AS t
			LEFT JOIN suki_c_message AS m ON (m.msgID = t.fmsgID)
			WHERE boardID = :board
			ORDER BY m.msgID DESC
			LIMIT :start, :limit', array(
			':limit' => $params['limit'],
			':start' => ($params['start'] * $params['limit']),
			':board' => $params['board'],
		));
	}
}
