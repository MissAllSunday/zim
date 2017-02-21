<?php

namespace Controllers;

class Cron extends Base
{
	function __construct()
	{
		$this->f3 = \Base::instance();
		$this->_defaultModels[] = 'cron';

		// Fake the route.
		$this->beforeRoute($this->f3);

		$this->simplePie = new \SimplePie;

		$this->simplePie->set_output_encoding($this->f3->get('ENCODING'));
		$this->simplePie->enable_cache(false);
		$this->simplePie->strip_htmltags(false);
	}

	function init()
	{
		$this->simplePie->init();

		// Is there a error? @todo log it.
		if ($this->simplePie->error())
			return 'error:'. $this->simplePie->error();

		$itemCount = 0;

		// Oldest first!
		$getItems = array_reverse($this->simplePie->get_items());

		// Go get'em tiger!
		foreach ($getItems as $item)
		{
			// If this item doesn't have a link or title, let's skip it
			if ($item->get_title() === null)
				continue;

			// Keyword search??
			if ($this->_models['cron']->keywords && !$this->_keywords($this->_models['cron']->keywords, $item->get_title() . ($item->get_content() !== null ? ' ' . $item->get_content() : '')))
				continue;

			// Check if this item has already been posted.
			$hash = $item->get_date('U');
			if ($this->_models['cron']->hash < $hash)
				continue;

			// No? then log it BUT only if the item is newer!
			elseif ($hash >= $this->_models['cron']->hash)
			{
				$this->_models['cron']->hash = $hash;

				$params = [
					'boardID' => $this->_models['cron']->boardID,
					'topicID' => ($this->_models['cron']->topicID ?: 0),
					'userID' => $this->_models['cron']->userID,
					'userName' => $this->_models['cron']->userName,
					'userIP' => '127.0.0.0',
					'tags' => $this->_models['cron']->tags,
				];

				// Start setting some values.
				$feedTitle = $this->simplePie->get_title() !== null ? $this->simplePie->get_title() : '';
				$body = $item->get_content() !== null ? $item->get_content() : $item->get_title();
				$title = $item->get_title();

				// These should be set as callbacks but I don't care!
				if($this->f3->exists('CRON.spoilerReference') && $path = stristr($body, $this->f3->get('CRON.spoilerReference'), true))
					$body = $path;

				// Is this an op topic?
				if (strpos($title, $this->f3->get('CRON.opFeed')) !== false)
					$body = $this->f3->get('txt.opLogo'). $body;

				$params['body'] =
		($item->get_permalink() !== null ? '<a href="' . $item->get_permalink() . '">' . $title . '</a>' : $title) . '
		' . ($item->get_date() !== null ? '<strong>' . $item->get_date() . '</strong>
		' : '') . '
		' . $body . '
		' . (!empty($this->_models['cron']->footer) ? $this->_models['cron']->footer : '');

				// Might have to update the subject for the single topic people
				$params['title'] = ($this->_models['cron']->topicPrefix ? $this->_models['cron']->topicPrefix . ' ' : '') . $title;

				$this->_models['message']->createEntry($params);
				$this->_models['message']->reset();
			}
		}

		$this->_models['cron']->itemCount++;
		$this->_models['cron']->save();
		$this->_models['cron']->reset();
	}

	function github()
	{
		// Yes, I am THAT lazy.
		$this->_models['cron']->load(['title = ?',  'github']);

		// Theres no such thing. @todo log it. How? I dunno...
		if ($this->_models['cron']->dry() || !$this->_models['cron']->enabled)
			return 'github cron disabled';

		$this->simplePie->set_feed_url($this->_models['cron']->url);
		echo 'set url: '. $this->_models['cron']->url . PHP_EOL;

		// Run.
		$this->init();
	}

	function manga()
	{
		$this->_models['cron']->load(['title = ?',  'manga']);

		// Theres no such thing.
		if ($this->_models['cron']->dry() || !$this->_models['cron']->enabled)
			return false;

		$this->simplePie->set_feed_url($this->_models['cron']->url);

		// Run.
		$this->init();
	}

	function spoiler()
	{
		$this->_models['cron']->load(['title = ?',  'spoiler']);

		// Theres no such thing.
		if ($this->_models['cron']->dry() || !$this->_models['cron']->enabled)
			return false;

		$this->simplePie->set_feed_url($this->_models['cron']->url);

		// Run.
		$this->init();
	}

	function blog()
	{
		$file = $this->f3->get('CRON.blogFile');
		$doc = new \DOMDocument;
		$doc->load($file);

		$message = $doc->documentElement->getElementsByTagName('message')->item(0);

		if (!empty($message) && is_object($message))
			$news = [
				'title' => $message->getElementsByTagName('title')->item(0)->nodeValue,
				'body' => $message->getElementsByTagName('body')->item(0)->nodeValue,
				'tags' => $message->getElementsByTagName('tags')->item(0)->nodeValue,
				'boardID' => $this->f3->get('CRON.blogBoardID'),
				'topicID' => $this->f3->get('CRON.blogTopicID'),
				'userID' => $this->f3->get('CRON.blogUserID'),
				'userName' => $this->f3->get('CRON.blogUserName'),
				'userIP' => '127.0.0.0',
			];

		else
			return false;

		$this->_models['message']->createEntry($news);

		// Remove the message.
		$doc->documentElement->removeChild($message);

		// Save it.
		$doc->saveXML();
		$doc->save($file);
		$this->_models['message']->createEntry($news);
		$this->_models['message']->reset();
		$this->_models['cron']->reset();
	}

	protected function _keywords($keywords, $string)
		{
			if (function_exists('mb_strtolower'))
				$string = mb_strtolower($string, $this->f3->get('ENCODING'));

			else
				$string = strtolower($string);

			if (!is_array($keywords))
				$keywords = explode(",", $keywords);

			foreach($keywords as $keyword)
			{
				if (function_exists('mb_strtolower'))
					$keyword = mb_strtolower($keyword, $this->f3->get('ENCODING'));

				else
					$keyword = strtolower($keyword);

				if (strpos($string, trim($keyword)) !== false)
					return true;
			}
			return false;
		}
}
