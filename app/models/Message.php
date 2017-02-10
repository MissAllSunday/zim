<?php

namespace Models;

class Message extends \DB\SQL\Mapper
{
	public static $rows = [
		'msgTime' => 0,
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
			SELECT m.msgTime, m.title, m.url, m.body, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar
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
		foreach ($entries as $k => $m)
			{
				$entries[$k]['desc'] = $f3->get('Tools')->metaDescription($m['body'], 60);
				$entries[$k]['date'] = $f3->get('Tools')->realDate($m['msgTime']);
				$entries[$k]['microDate'] =  $f3->get('Tools')->microdataDate($m['msgTime']);

				if (empty($entries[$k]['avatar']))
					$entries[$k]['avatar'] = $f3->get('BASE') .'/identicon/'. $m['userName'];
			}

			return $entries;
	}

	function entryInfo($id = 0, $limit = 10)
	{
		$f3 = \Base::instance();
		$r = [];

		$r = $this->db->exec('
			SELECT t.lmsgID, m.msgID, m.msgTime, m.title, m.tags, m.url, m.boardID, m.body, b.title AS boardTitle, b.url AS boardUrl, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar, (SELECT COUNT(*)
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

		$r = $r[0];

		// Lets avoid issues.
		$r['max_count'] = (int) $r['max_count'];
		$r['pages'] = (int) ceil($r['max_count'] / $limit);

		// Build the pagination stuff.
		if ($r['max_count'] > $limit)
			$r['last_url'] = $r['url'] . '/page/' . $r['pages'] .'#msg'. $r['lmsgID'];

		else
			$r['last_url'] = $r['url'] .'#msg'. $r['lmsgID'];

		$r['date'] = $f3->get('Tools')->realDate($r['msgTime']);
		$r['microDate'] =  $f3->get('Tools')->microdataDate($r['msgTime']);

		if (empty($r['avatar']))
			$r['avatar'] = $f3->get('BASE') .'/identicon/'. $r['userName'];

		return $r;
	}

	function single($params = [])
	{
		$f3 = \Base::instance();
		$data = [];
		$page = $params[':start'];
		$params[':start'] = $params[':start'] * $params[':limit'];

		$data = $this->db->exec('
			SELECT m.msgID, m.topicID, m.body, m.title, m.url, m.msgTime, IFNULL(u.userID, 0) AS userID, IFNULL(u.userName, m.userName) AS userName, IFNULL(u.avatar, "") AS avatar
			FROM suki_c_message AS m
			LEFT JOIN suki_c_user AS u ON (u.userID = m.userID)
			WHERE topicID = :topic
				AND msgID != :msg
			ORDER BY msgID ASC
			LIMIT :start, :limit', $params);

		foreach ($data as $k => $v)
		{
			if (empty($v['avatar']))
				$data[$k]['avatar'] = $f3->get('BASE') .'/identicon/'. $v['userName'];

			if (!empty($page))
				$data[$k]['url'] .= '/page/'. $page .'#msg'. $v['msgID'];

			else
				$data[$k]['url'] .= '#msg'. $v['msgID'];

			$data[$k]['date'] = $f3->get('Tools')->realDate($v['msgTime']);
			$data[$k]['microDate'] =  $f3->get('Tools')->microdataDate($v['msgTime']);
		}

		return $data;
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

		$params = array_merge(self::$rows, $params);

		// Clean up the tags.
		if (!empty($params['tags']))
			$params['tags'] =  $f3->get('Tools')->commaSeparated($params['tags']);

		// Be nice.
		$params = array_map(function($var) use($f3){
			return $f3->get('Tools')->sanitize($var);
		}, array_intersect_key($params, self::$rows));

		// These pesky fields need to be assigned at this point and place in time!
		$params['msgTime'] = time();
		$params['userIP'] = $f3->ip();

		$this->copyFrom($params);

		// Get the newly created msgID.
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

		// Clean up.
		$this->save();
		$topicModel->reset();
	}
}
