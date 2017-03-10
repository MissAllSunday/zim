<?php

namespace Models;

class Message extends \DB\SQL\Mapper
{
	public static $rows = [
		'msgID' => 0,
		'msgTime' => 0,
		'msgModified' => 0,
		'reason' => '',
		'reasonby' => '',
		'boardID' => 0,
		'topicID' => 0,
		'approved' => 1,
		'userID' => 0,
		'userName' => 'Guest',
		'userEmail' => '',
		'userIP' => '',
		'title' => '',
		'body' => '',
		'tags' => '',
		'url' => '',
	];
	public static $topicRows = [
		'locked' => 0,
		'sticky' => 0,
	];

	function __construct(\DB\SQL $db)
	{
				parent::__construct($db, \Base::instance()->get('_db.prefix') . 'message');
	}

	function entries($params = [])
	{
		$f3 = \Base::instance();
		$entries = [];

		$entries = $this->db->exec('
			SELECT t.locked, t.sticky, m.msgTime, m.title, m.msgModified, m.reason, m.reasonBy, m.url, m.body, m.userEmail, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar, (u.last_active >= UNIX_TIMESTAMP() - 300) AS isOnline
			FROM suki_c_topic AS t
			LEFT JOIN suki_c_message AS m ON (m.msgID = t.fmsgID)
			LEFT JOIN suki_c_user AS u ON (u.userID = m.userID)
			WHERE t.boardID = :board
			ORDER BY m.msgID DESC
			LIMIT :start, :limit', $params, 300);

		// Add a nice description and a real date.
		foreach ($entries as $k => $v)
			$entries[$k] = $f3->get('Tools')->prepareData($v);

		return $entries;
	}

	function entryInfo($id = 0)
	{
		$f3 = \Base::instance();
		$r = [];

		$r = $this->db->exec('
			SELECT t.locked, t.sticky, t.lmsgID, m.msgID, m.topicID, m.msgTime, m.title, m.msgModified, m.reason, m.reasonBy, m.tags, m.url, m.boardID, m.body, b.title AS boardTitle, b.url AS boardUrl, m.userEmail, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar, (u.last_active >= UNIX_TIMESTAMP() - 300) AS isOnline, (SELECT COUNT(*)
				FROM '. $this->table() .'
				WHERE topicID = :topic) as max_count
			FROM suki_c_topic AS t
			LEFT JOIN suki_c_message AS m ON (m.msgID = t.fmsgID)
			LEFT JOIN suki_c_board AS b ON (b.boardID = t.boardID)
			LEFT JOIN suki_c_user AS u ON (u.userID = m.userID)
			WHERE t.topicID = :topic
			ORDER BY m.msgID DESC
			LIMIT 1', [
				':topic' => $id,
			], 300);

		if (empty($r))
			return [];

		$r = $r[0];

		return $f3->get('Tools')->prepareData($r);
	}

	function latestTopics($limit = 10, $ttl = 300)
	{
		$f3 = \Base::instance();
		$data = [];

		// Cache is set on call.
		$data = $this->db->exec('
			SELECT t.locked, t.sticky, t.lmsgID, m.msgID, m.topicID, m.msgTime, m.title, m.msgModified, m.reason, m.reasonBy, m.tags, m.url, m.boardID, m.body, b.title AS boardTitle, b.url AS boardUrl, m.userEmail, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar, (u.last_active >= UNIX_TIMESTAMP() - 300) AS isOnline, (SELECT COUNT(*)
				FROM '. $this->table() .'
				WHERE topicID = m.topicID) as max_count
			FROM '. $this->table() .' AS m
			LEFT JOIN suki_c_topic AS t ON (t.fmsgID = m.msgID)
			LEFT JOIN suki_c_board AS b ON (b.boardID = t.boardID)
			LEFT JOIN suki_c_user AS u ON (u.userID = m.userID)
			ORDER BY t.topicID DESC
			LIMIT :limit', [':limit' => $limit], $ttl);

		if (empty($data))
			return [];

		foreach ($data as $k => $v)
		{
			if (empty($data[$k]['topicID']))
			{
				unset($data[$k]);
				continue;
			}

			$data[$k] = $f3->get('Tools')->prepareData($v);
		}

		return $data;
	}

	function latestMessages($limit = 5, $ttl = 300)
	{
		$f3 = \Base::instance();
		$data = [];

		$data = $this->db->exec('
			SELECT t.locked, t.sticky, t.lmsgID, m.msgID, m.topicID, m.msgTime, m.title, m.tags, m.url, m.boardID, b.title AS boardTitle, b.url AS boardUrl, m.userEmail, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar, (u.last_active >= UNIX_TIMESTAMP() - 300) AS isOnline, (SELECT COUNT(*)
				FROM '. $this->table() .'
				WHERE topicID = m.topicID) as max_count
			FROM '. $this->table() .' AS m
			LEFT JOIN suki_c_topic AS t ON (t.fmsgID = m.msgID)
			LEFT JOIN suki_c_board AS b ON (b.boardID = t.boardID)
			LEFT JOIN suki_c_user AS u ON (u.userID = m.userID)
			ORDER BY m.msgID DESC
			LIMIT :limit', [':limit' => $limit], $ttl);

		foreach ($data as $k => $r)
			$data[$k] = $f3->get('Tools')->prepareData($r);

		return $data;
	}

	function comments($params = [])
	{
		$f3 = \Base::instance();
		$data = [];

		$result = $this->db->exec('
			SELECT m.msgID, m.topicID, m.body, m.title, m.url, m.msgTime, m.msgModified, m.reason, m.reasonBy, m.userEmail, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar, (u.last_active >= UNIX_TIMESTAMP() - 300) AS isOnline, (SELECT COUNT(*)
				FROM '. $this->table() .'
				WHERE topicID = m.topicID) as max_count, t.locked, t.sticky
			FROM '. $this->table() .' AS m
			LEFT JOIN suki_c_user AS u ON (u.userID = m.userID)
			LEFT JOIN suki_c_topic AS t ON (t.topicID = m.topicID)
			WHERE m.topicID = :topic
			ORDER BY msgID ASC
			LIMIT :start, :limit', $params);

		foreach ($result as $k => $v)
			$data[$v['msgID']] = $f3->get('Tools')->prepareData($v);

		return $data;
	}

	function deleteMessages($ids = [])
	{
		$this->db->exec('DELETE FROM '. $this->table() .' WHERE msgID IN(:ids)', [':ids' => implode(',', $ids)]);
	}

	function getMessageIDs($id = 0)
	{
		$ids = [];
		$data = $this->db->exec('
			SELECT msgID
			FROM '. $this->table() .'
			WHERE topicID = :topic',[
				':topic' => $id,
			]);

		foreach ($data as $value)
			$ids[] = (int) $value['msgID'];

		return $ids;
	}

	function createEntry($params = [])
	{
		if (empty($params))
			return false;

		// Make sure we are working with fresh data.
		$this->reset();

		// Need these. For reasons!
		$f3 = \Base::instance();
		$topicModel = new \Models\Topic($f3->get('DB'));
		$userModel = new \Models\User($f3->get('DB'));

		$params = array_merge(self::$rows, $params);

		// Clean up the tags.
		if (!empty($params['tags']))
			$params['tags'] =  $f3->get('Tools')->commaSeparated($params['tags']);

		$topicParams = array_map(function($var) use($f3){
			return $f3->get('Tools')->sanitize($var);
		}, array_intersect_key($params, self::$topicRows));

		// Be nice.
		$params = array_map(function($var) use($f3){
			return $f3->get('Tools')->sanitize($var);
		}, array_intersect_key($params, self::$rows));

		// These pesky fields need to be assigned at this point and place in time!
		if (!empty($params['msgID']))
		{
			$params['msgModified'] = time();
			$params['reasonBy'] = $f3->get('currentUser')->userName;
		}

		$params['msgTime'] = time();
		$params['userIP'] = $f3->ip();

		// Are we editing?
		if (!empty($params['msgID']))
		{
			$this->load(['msgID = ?', $params['msgID']]);
			$params['msgTime'] = $this->msgTime;
		}

		$this->copyFrom($params);

		// Save.
		$this->save();

		// Is a reply?
		if (!empty($params['topicID']))
		{
			$topicInfo = $topicModel->load(['topicID = ?', $params['topicID']]);

			$topicModel->lmsgID = $this->msgID;
		}

		// No? then create it.
		else
		{
			$topicModel->fmsgID = $this->msgID;
			$topicModel->lmsgID = $this->msgID;
			$topicModel->boardID = $this->boardID;
			$topicModel->solved = 0;
		}

		$topicModel->copyFrom($topicParams);

		// Done.
		$topicModel->save();

		// Now that we have the topic ID, create the slug.
		$this->url = $f3->get('Tools')->slug($params['title']) .'-'. $topicModel->topicID;

		// And update the topic.
		$this->topicID = $topicModel->topicID;

		// Lastly, if theres an user, update the user table.
		if($this->userID)
		{
			$userModel->load(['userID = ?', $this->userID]);
			$userModel->posts++;
			$userModel->lmsgID = $this->msgID;
			$userModel->save();
		}

		// Clean up.
		$this->save();
		$topicModel->reset();
	}
}
