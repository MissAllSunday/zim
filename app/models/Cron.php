<?php

namespace Models;

class Cron  extends Base
{
	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, 'suki_c_cron');
	}

	function spoiler($f3)
	{
		// Get the file.
		$file = $f3->get('BASE') .'/'. $f3->get('CRON.blogFile');

		// No can do.
		if (!file_exists($file) || !is_writable($file))
			return false;

		$news = array();

		// XML because reasons!
		$doc = new DOMDocument;
		$doc->load($file);
		$message = $doc->documentElement->getElementsByTagName('message')->item(0);
		if (!empty($message) && is_object($message))
			$news = array(
				'title' => $message->getElementsByTagName('title')->item(0)->nodeValue,
				'body' => $message->getElementsByTagName('body')->item(0)->nodeValue,
				'tags' => $message->getElementsByTagName('tags')->item(0)->nodeValue,
			);

		else
			return false;

		// All done? post it then!
		$this->_models['message']->createEntry([
			'msgTime' => time(),
			'boardID' => $f3->get('CRON.blogBoardID'),
			'topicID' => 0,
			'approved' => 1,
			'userID' => $f3->get('CRON.blogUserID'),
			'userName' => $f3->get('CRON.blogUserName'),
			'userIP' => $f3->get('IP'),
			'title' => $news['title'],
			'body' => $news['body'],
			'tags' =>  $news['tags'],
		]);

		// Remove the message.
		$doc->documentElement->removeChild($message);

		// Save it.
		$doc->saveXML();
		$doc->save($file);

		return $news;
	}
}
