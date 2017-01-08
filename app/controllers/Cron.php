<?php

namespace Controllers;

class Blog extends Base

	function __construct()
	{
		$this->f3 = \Base::instance();
		$this->model = new \Models\Cron($this->f3->get('DB'));

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
		}
	}

	function github()
	{
		// Yes, I am THAT lazy.
		$this->model->load(['title = ?'], __FUNCTION__);

		// Theres no such thing.
		if ($this->model->dry())
			return false;

		$this->simplePie->set_feed_url($this->model->url);

		// Run.
		$this->run();
	}

	function manga()
	{

	}

	function spoilers()
	{

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
