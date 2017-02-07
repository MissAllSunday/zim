<?php

namespace Models;

class Message extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, 'suki_c_message');
	}

	function entries($params = [])
	{
		$f3 = \Base::instance();
		$entries = [];

		$entries = $this->db->exec('
			SELECT m.msgTime, m.title, m.url, m.body, m.userID, u.avatar, u.userName
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
			}

			return $entries;
	}

	function entryInfo($id = 0, $limit = 10)
	{
		$f3 = \Base::instance();
		$r = [];

		$r = $this->db->exec('
			SELECT m.msgID, m.msgTime, m.title, m.tags, m.url, m.boardID, m.body, b.title AS boardTitle, b.url AS boardUrl, u.userID, u.userName, u.avatar, (SELECT COUNT(*)
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

		// Build the pagination stuff.
		if ($r['max_count'] > $limit)
			$r['last_url'] = $r['url'] . '/page/' . (int) ($r['max_count'] / ($limit));

		else
			$r['last_url'] = $r['url'];

		$r['date'] = $f3->get('Tools')->realDate($r['msgTime']);
		$r['microDate'] =  $f3->get('Tools')->microdataDate($r['msgTime']);

		return $r;
	}

	function single($params = [])
	{
		$f3 = \Base::instance();
		$data = [];
		$page = $params[':start'];
		$params[':start'] = $params[':start'] * $params[':limit'];

		$data = $this->db->exec('
			SELECT m.msgID, m.topicID, m.body, m.title, m.url, m.msgTime, u.userID, u.userName, u.avatar
			FROM suki_c_message AS m
			LEFT JOIN suki_c_user AS u ON (u.userID = m.userID)
			WHERE topicID = :topic
				AND msgID != :msg
			ORDER BY msgID ASC
			LIMIT :start, :limit', $params);

		foreach ($data as $k => $v)
		{
			if (!empty($page))
				$data[$k]['url'] .= '/page/'. $page .'#msg'. $v['msgID'];

			$data[$k]['date'] = $f3->get('Tools')->realDate($v['msgTime']);
			$data[$k]['microDate'] =  $f3->get('Tools')->microdataDate($v['msgTime']);
		}

		return $data;
	}

	function createEntry($params = [])
	{
		if (empty($params))
			return false;

		// Need these. For reasons!
		$f3 = \Base::instance();
		$topicModel = new \Models\Topic($f3->get('DB'));

		// Set some defaults.
		$defaults = [
			'msgTime' => time(),
			'boardID' => 0,
			'topicID' => 0,
			'approved' => 1,
			'userID' => 0,
			'userName' => 'Guest',
			'userIP' => $f3->get('IP'),
			'title' => '',
			'body' => '',
			'tags' => '',
			'url' => '',
		];

		$params = array_merge($defaults, $params);

		// Clean up the tags.
		if (!empty($params['tags']))
			$params['tags'] =  $f3->get('Tools')->commaSeparated($params['tags']);

		// Be nice.
		$params['body'] = $f3->get('Tools')->sanitize($f3->get('Tools')->parser($params['body']));

		$this->copyFrom($params);

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

		// Now that we have the message ID, create the slug.
		$this->url = $f3->get('Tools')->slug($params['title']) .'-'. $topicModel->topicID .'#msg'. $this->msgID;

		// And update the topic.
		$this->topicID = $topicModel->topicID;

		// Save.
		$this->save();
	}
}
