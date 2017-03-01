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

	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, 'suki_c_message');
	}

	function entries($params = [])
	{
		$f3 = \Base::instance();
		$entries = [];

		$entries = $this->db->exec('
			SELECT m.msgTime, m.title, m.msgModified, m.reason, m.reasonBy, m.url, m.body, m.userEmail, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar
			FROM suki_c_topic AS t
			LEFT JOIN suki_c_message AS m ON (m.msgID = t.fmsgID)
			LEFT JOIN suki_c_user AS u ON (u.userID = m.userID)
			WHERE t.boardID = :board
			ORDER BY m.msgID DESC
			LIMIT :start, :limit', [
			':limit' => $params['limit'],
			':start' => ($params['start'] * $params['limit']),
			':board' => $params['board'],
		]);

		// Add a nice description and a real date.
		foreach ($entries as $k => $v)
			$entries[$k] = $this->prepareData($v);

			return $entries;
	}

	function entryInfo($id = 0)
	{
		$f3 = \Base::instance();
		$r = [];

		$r = $this->db->exec('
			SELECT t.lmsgID, m.msgID, m.topicID, m.msgTime, m.title, m.msgModified, m.reason, m.reasonBy, m.tags, m.url, m.boardID, m.body, b.title AS boardTitle, b.url AS boardUrl, m.userEmail, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar, (SELECT COUNT(*)
				FROM suki_c_message
				WHERE topicID = :topic) as max_count
			FROM suki_c_topic AS t
			LEFT JOIN suki_c_message AS m ON (m.msgID = t.fmsgID)
			LEFT JOIN suki_c_board AS b ON (b.boardID = t.boardID)
			LEFT JOIN suki_c_user AS u ON (u.userID = m.userID)
			WHERE t.topicID = :topic
			ORDER BY m.msgID DESC
			LIMIT 1', [
				':topic' => $id,
			]);

		if (empty($r))
			return [];

		$r = $r[0];

		// Lets avoid issues.
		$r['max_count'] = (int) $r['max_count'];
		$r['pages'] = (int) ceil($r['max_count'] / $f3->get('paginationLimit'));

		// Build the pagination stuff.
		if ($r['max_count'] > $limit)
			$r['last_url'] = $r['url'] . (($r['pages'] - 1) != 0 ? '/page/' . ($r['pages'] - 1) : '') .'#msg'. $r['lmsgID'];

		else
			$r['last_url'] = $r['url'] .'#msg'. $r['lmsgID'];

		$r = $this->prepareData($r);

		return $r;
	}

	function latestMessages($limit = 5)
	{
		$f3 = \Base::instance();

		$data = $this->db->exec('
			SELECT t.lmsgID, m.msgID, m.topicID, m.msgTime, m.title, m.tags, m.url, m.boardID, b.title AS boardTitle, b.url AS boardUrl, m.userEmail, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar, (SELECT COUNT(*)
				FROM suki_c_message
				WHERE topicID = m.topicID) as max_count
			FROM suki_c_message AS m
			LEFT JOIN suki_c_topic AS t ON (t.fmsgID = m.msgID)
			LEFT JOIN suki_c_board AS b ON (b.boardID = t.boardID)
			LEFT JOIN suki_c_user AS u ON (u.userID = m.userID)
			ORDER BY m.msgID DESC
			LIMIT :limit', [
				':limit' => $limit,
			]);

		foreach ($data as $k => $r)
		{
			// Lets avoid issues.
			$r['pages'] = (int) ceil((int) $r['max_count'] / $f3->get('paginationLimit'));

			// Build the pagination stuff.
			if ($r['max_count'] > $f3->get('paginationLimit'))
				$r['last_url'] = $r['url'] . '/page/' . ($r['pages'] - 1) .'#msg'. $r['msgID'];

			else
				$r['last_url'] = $r['url'] .'#msg'. $r['lmsgID'];

			$data[$k] = $this->prepareData($r);
		}

		return $data;
	}

	function single($params = [])
	{
		$f3 = \Base::instance();
		$data = [];
		$page = $params[':start'];
		$params[':start'] = $params[':start'] * $params[':limit'];

		$data = $this->db->exec('
			SELECT m.msgID, m.topicID, m.body, m.title, m.url, m.msgTime, m.msgModified, m.reason, m.reasonBy, m.userEmail, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar
			FROM suki_c_message AS m
			LEFT JOIN suki_c_user AS u ON (u.userID = m.userID)
			WHERE topicID = :topic
				AND msgID != :msg
			ORDER BY msgID ASC
			LIMIT :start, :limit', $params);

		foreach ($data as $k => $v)
		{
			$data[$k] = $this->prepareData($v);

			if (!empty($page))
				$data[$k]['url'] .= '/page/'. $page .'#msg'. $v['msgID'];

			else
				$data[$k]['url'] .= '#msg'. $v['msgID'];
		}

		return $data;
	}

	function deleteMessages($ids = [])
	{
		$this->db->exec('DELETE FROM suki_c_message WHERE msgID IN(:ids)', [':ids' => implode(',', $ids)]);
	}

	function getMessageIDs($id = 0)
	{
		$ids = [];
		$data = $this->db->exec('
			SELECT msgID
			FROM suki_c_message
			WHERE topicID = :topic',[
				':topic' => $id,
			]);

		foreach ($data as $value)
			$ids[] = (int) $value['msgID'];

		return $ids;
	}

	function prepareData($d = [])
	{
		$f3 = \Base::instance();

		// Provide a generic avatar
		if (empty($d['avatar']))
			$d['avatar'] = !empty($d['userEmail']) ? \Gravatar::instance()->get($d['userEmail']) : $f3->get('BASE') .'/identicon/'. $d['userName'];

		// Parse the body
		if (!empty($d['body']))
		{
			// Create a description
			$d['desc'] = $f3->get('Tools')->metaDescription($d['body'], 60);

			$d['body'] = $f3->get('Tools')->parser($d['body']);
		}

		// Get the detes
		$d['date'] = $f3->get('Tools')->getDate($d['msgTime']);
		$d['microDate'] =  date("c", $d['msgTime']);

		return $d;
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
