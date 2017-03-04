<?php

namespace Controllers;

class Xml extends Base
{
	function atom(\Base $f3, $params)
	{
		// Get the items.
		$items = $this->_models['message']->latestTopics(30), 3600;

		// Get the update time.
		$set('atomUpdated', $items[0]['microDate']);
		$f3->set('atomItems', $this->_models['message']->latestTopics(30), 3600);

		header("Content-Type: application/xml");
		echo \Template::instance()->render('atom.xml');
	}

	function sitemap(\Base $f3, $params)
	{
		$f3->set('atomItem', $this->_models['message']->latestTopics(100), 86400);

		header("Content-Type: application/xml");
		echo \Template::instance()->render('sitemap.xml');
	}
}
