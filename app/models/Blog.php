<?php

namespace Models;

class Blog extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, 'suki_c_message');
	}

	function entries($params = [])
	{
		return $this->db->exec('
			SELECT m.msgTime, m.title, m.url
			FROM suki_c_topic AS t
			LEFT JOIN suki_c_message AS m ON (m.msgID = t.fmsgID)
			WHERE t.boardID = :board
			ORDER BY m.msgID DESC
			LIMIT :start, :limit', [
			':limit' => $params['limit'],
			':start' => ($params['start'] * $params['limit']),
			':board' => $params['board'],
		]);
	}

	function entryInfo($id = 0)
	{
		return $this->db->exec('
			SELECT m.msgID, m.msgTime, m.title, m.url, m.boardID, m.body, b.title AS boardTitle, b.url AS boardUrl
			FROM suki_c_topic AS t
			LEFT JOIN suki_c_message AS m ON (m.msgID = t.fmsgID)
			LEFT JOIN suki_c_board AS b ON (b.boardID = t.boardID)
			WHERE t.topicID = :topic
			ORDER BY m.msgID DESC
			LIMIT 1', [
				':topic' => $id,
			], 600);
	}

	function single($params = [])
	{
		return $this->db->exec('
			SELECT topicID, body, title, url, tags, msgTime
			FROM suki_c_message
			WHERE topicID = :topic
				AND msgID != :msg
			ORDER BY msgID DESC
			LIMIT :start, :limit', $params);
	}

	function createEntry($params = [])
	{
		$this->reset();

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
		$params['tags'] =  $f3->get('Tools')->commaSeparated($params['tags']);

		foreach($params as $k => $v)
			$this->{$k} = $v;

		// Save.
		$this->save();

		// Is a reply?
		if (!empty($params['topicID']))
		{
			$topicInfo = $topicModel->load(['topicID = ?', $params['topicID']]);

			$topicModel->lmsgID = $this->msgID;
		}

		// No? then create it.
		$topicModel->fmsgID = $this->msgID;
		$topicModel->lmsgID = $this->msgID;
		$this->topicMode->boardID = $this->boardID;
		$topicModel->solved = 0;

		// Done.
		$topicModel->save();

		// Now that we have the message ID, create the slug.
		$this->url = $f3->get('Tools')->slug($this->title) .'-'. $this->msgID;

		// And update the topic.
		$this->topicID = $topicModel->topicID;

		// Save again.
		$this->save();

		// Return the newly msg obj.
		return $this;
	}
}
