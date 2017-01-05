<?php

namespace Controllers;

class Post extends Auth
{
	protected $_fields = [
		'tags',
		'title',
		'body',
		'boardID',
	];

	function __construct()
	{
		$this->model = new \Models\Blog(\Base::instance()->get('DB'));
		$this->topicModel = new \DB\SQL\Mapper(\Base::instance()->get('DB'),'suki_c_topic');
	}

	function post(\Base $f3, $params)
	{
		// If theres SESSION data, use that.

		// Check we got a valid boardID

		// And a topic if needed.

		// We need these for the editor stuff!
		$f3->push('site.customJS', 'summernote.min.js');
		$f3->push('site.customJS', 'summernote-image-attributes.js');
		$f3->push('site.customCSS', 'summernote.css');

		$f3->set('content','post.html');
		echo \Template::instance()->render('home.html');
	}

	function create(\Base $f3, $params)
	{
		$errors = [];

		$this->model->reset();

		// Validation and sanitization
		$data = array_map(function($var) use($f3){
			return $f3->get('Tools')->sanitize($var);
		}, array_intersect_key($f3->get('POST'), $this->_fields));

		// Token check.
		if ($f3->get('POST.token')!= $f3->get('SESSION.csrf'))
			$errors[] = 'bad_token';

		// Moar handpicked!
		foreach ($data as $k => $v)
			if(empty($v))
				$errors[] = 'empty_'. $k;

		// Clean up the tags.
		if(!empty($data['tags']))
			$data['tags'] = $f3->get('Tools')->commaSeparated($data['tags'], 'alpha');

		// Lets take five shall we?
		if (!empty($errors))
		{
			// Save the data.
			$f3->set('SESSION.posting', $data);

			\Flash::instance()->addMessage($errors, 'danger');
			return $f3->reroute('/post/'. $data['boardID'] .'/'. $data['topicID']);
		}

		// Check that the board really exists.
		if (!$this->model->findone(array('userID = ?', $id));
		{
			\Flash::instance()->addMessage($f3->get('txt.invalid_board'), 'danger');

			return $f3->reroute('/');
		}

		// If theres a topic ID, make sure it really exists...
	}

	function preview(\Base $f3, $params)
	{

	}
}
