<?php

namespace Controllers;

class Xml extends Base
{
	function atom(\Base $f3, $params)
	{
		// Fuck you datadog
		if ($f3->get('AGENT') == 'Datadog/1.0')
		{
			$competitors = $f3->get('datadog');
			$randomCompetitor = $competitors[mt_rand(0, count($competitors) - 1)];

			return $f3->reroute($randomCompetitor);
		}

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
		$f3->set('sitemapItems', $this->_models['message']->latestTopics(200, 86400));

		// Boards
		$f3->set('sitemapBoards', $this->_models['board']->getBoards());

		echo \Template::instance()->render('sitemap.xml', 'application/xml');
	}
}
