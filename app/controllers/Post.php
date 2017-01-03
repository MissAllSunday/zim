<?php

namespace Controllers;

class Post extends Auth
{
	function __construct()
	{
		$this->model = new \Models\Blog(\Base::instance()->get('DB'));
	}

	function create()
	{

	}

	function preview()
	{

	}
}
