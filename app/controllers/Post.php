<?php

namespace Controllers;

class Post extends Auth
{
	function __construct()
	{
		$this->model = new \Models\Blog(\Base::instance()->get('DB'));
	}

	function create(\Base $f3, $params)
	{
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
