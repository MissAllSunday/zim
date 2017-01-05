<?php

namespace Controllers;

class Post extends Auth
{
	function __construct()
	{
		$this->model = new \Models\Blog(\Base::instance()->get('DB'));
		$this->topicModel = new \DB\SQL\Mapper(\Base::instance()->get('DB'),'suki_c_topic');
	}

	function create(\Base $f3, $params)
	{
		// Is the re any ac tual data?
		if ($f3->get('VERB') == 'POST')
		{
			$this->model->reset();

			// Validation and sanitization
			$valid = \Validate::is_valid($f3->get('POST'), array(
				'tags' => 'max_len,250',
				'title' => 'required|max_len,250|min_len,5',
				'body' => 'required',
			));

			// Error handler
			if ($valid === true)
			{
				// Handpicked
				$tags = $f3->get('Tools')->sanitize($f3->get('POST.tags'));
				$title = $f3->get('Tools')->sanitize($f3->get('POST.title'));
				$body = $f3->get('Tools')->sanitize($f3->get('POST.body'));
			}

			else
			{

			}

			// Topic model to do some further checks

		}

		// We need these for the editor stuff!
		$f3->push('site.customJS', 'summernote.min.js');
		$f3->push('site.customJS', 'summernote-image-attributes.js');
		$f3->push('site.customCSS', 'summernote.css');

		$f3->set('content','post.html');
		echo \Template::instance()->render('home.html');
	}

	function preview(\Base $f3, $params)
	{

	}
}
