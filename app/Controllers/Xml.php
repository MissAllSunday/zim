<?php

namespace Controllers;

class Xml extends Base
{
	function __construct()
	{
		header("Content-Type: application/xml");
	}

	function atom(\Base $f3, $params)
	{
		// Get the items.
		$f3->set('atomItem', $this->_models['message']->latestTopics(30), 3600);
		echo \Template::instance()->render('atom.xml');
	}

	function sitemap(\Base $f3, $params)
	{
		$f3->set('atomItem', $this->_models['message']->latestTopics(100), 86400);
		echo \Template::instance()->render('sitemap.xml');
	}
}
