<?php

namespace Controllers;

class Xml extends Base
{
	function atom(\Base $f3, $params)
	{
		// Get the items.
		$items = $this->_models['message']->latestTopics(30);

		// Get the update time.
		$f3->set('atomUpdated', $items[0]['microDate']);
		$f3->set('atomItems', $items, 3600);

		echo \Template::instance()->render('atom.xml', 'application/xml');
	}

	function sitemap(\Base $f3, $params)
	{
		// Get the items.
		$f3->set('sitemapItems', $this->_models['message']->latestTopics(100), 3600);

		echo \Template::instance()->render('sitemap.xml', 'application/xml');
	}
}
