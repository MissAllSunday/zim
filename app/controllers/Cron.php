<?php

namespace Controllers;

class Blog extends Base

	function __construct()
	{
		$this->f3 = \Base::instance();
		$this->model = new \Models\Cron($this->f3->get('DB'));
		$this->cronLogModel = new \DB\SQL\Mapper($this->f3->get('DB'),'suki_c_cronlog');

		$this->simplePie = new SimplePie;

		$this->simplePie->set_output_encoding($this->f3->get('ENCODING'));
		$this->simplePie->strip_htmltags(false);
	}

	function init()
	{
		$this->simplePie->init();

		// Is there a error? @todo log it.
		if ($this->simplePie->error())
			return false;

		$blogModel = $this->model = new \Models\Blog($this->f3->get('DB'));
		$itemCount = 0;
		$getItems = $rss_data->get_items();
		krsort($getItems);

		// Go get'em tiger!
		foreach ($get_items as $item)
		{
			// Do we have a cap on how many to import?
			if ($this->model->itemLimit && $itemCount >= $this->model->itemLimit)
				continue 1;

			// If this item doesn't have a link or title, let's skip it
			if ($item->get_title() === null)
				continue;

			// Keyword search??
			if ($this->model->keywords && !$this->_keywords($this->model->keywords, $item->get_title() . ($item->get_content() !== null ? ' ' . $item->get_content() : '')))
				continue;

			// Check if this item has already been posted.
			$hash = md5($item->get_title());
			$this->cronLogModel->reset();
			$this->cronLogModel->load(['hash = ?', $hash])
			if (!$this->cronLogModel->dry())
				continue;

			// No? then log it.
			else
			{
				$this->cronLogModel->hash = $hash;
				$this->cronLogModel->save();
				$this->cronLogModel->reset();
			}

			$params = [
				'boardID' => $this->model->boardID,
				'topicID' => ($this->model->topicID ?: 0),
				'userID' => $this->model->userID,
				'userName' => $this->model->userName,
				'userIP' => '127.0.0.0',
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
	' . (!empty($this->model->footer) ? $this->model->footer : '');

			// Might have to update the subject for the single topic people
			$params['title'] = ($this->model->topicPrefix) ? $this->model->topicPrefix . ' ' : '') . ($this->model->singleTopic && !$this->model->topicID && !empty($feedTitle) ? $feedTitle : $title);

			$blogModel->createEntry($data);
			$blogModel->reset();
		}
	}

	function github()
	{
		// Yes, I am THAT lazy.
		$this->model->load(['title = ?'], __FUNCTION__);

		// Theres no such thing. @todo log it. How? I dunno...
		if ($this->model->dry() || !$this->model->enbled)
			return false;

		$this->simplePie->set_feed_url($this->model->url);

		// Run.
		$this->run();
	}

	function manga()
	{
		$this->model->load(['title = ?'], __FUNCTION__);

		// Theres no such thing.
		if ($this->model->dry() || !$this->model->enbled)
			return false;

		$this->simplePie->set_feed_url($this->model->url);

		// Run.
		$this->run();
	}

	function spoilers()
	{
		$this->model->load(['title = ?'], __FUNCTION__);

		// Theres no such thing.
		if ($this->model->dry() || !$this->model->enbled)
			return false;

		$this->simplePie->set_feed_url($this->model->url);

		// Run.
		$this->run();
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
