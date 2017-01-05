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

		// Moar handpicked!
		foreach ($data as $k => $v)
			if(empty($v))
				$errors[] = 'empty_'. $k;

		// Lets take five shall we?
		if (!empty($errors))
		{
			\Flash::instance()->addMessage($errors, 'danger');
			return $f3->reroute('/login/'. $data['boardID'] .'/'. $data['topicID']);
		}
	}

	function preview(\Base $f3, $params)
	{

	}
}
