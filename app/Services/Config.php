<?php

namespace Services;

class Config
{
	function __construct(\Base $f3)
	{
		$this->f3 = $f3;
	}

	function init()
	{
		// This should be automatically set.... @todo
		$this->f3->set('Tools', new \Services\Tools($this->f3));

		// Set default metadata tags and/or other common HTML tags.
		$this->f3->set('site.currentUrl', $this->f3->get('URL'));
		$this->f3->set('site.metaTitle', 'Miss All Sunday - ');
		$this->f3->set('site.keywords', $this->f3->get('txt.site_keywords'));
		$this->f3->set('site.description', $this->f3->get('txt.site_desc'));

		// Declare these as an empty array.
		$this->f3->set('site.customJS', []);
		$this->f3->set('site.customExternalJS', []);
		$this->f3->set('site.customCSS', []);
	}
}
