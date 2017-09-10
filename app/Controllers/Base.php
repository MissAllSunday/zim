<?php

namespace Controllers;

class Base
{
	public $_models = [];
	protected $_defaultModels = ['auth', 'message', 'user', 'allow', 'board'];

	public function beforeRoute($f3)
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

		// Gotta stay classy...
		foreach ($this->_defaultModels as $m)
		{
			// ...OK no....
			$class = '\Models\\'. ucfirst($m);
			$this->_models[$m] = new $class($f3->get('DB'));
		}

		// Recurrent user? @todo find out why the cookie isn't been pick up
		$this->_models['auth']->login();

		// Permissions.
		$can = [];

		foreach ($this->_models['allow']->getAll() as $name => $groups)
			$can[$name] = $this->_models['allow']->can($name);

		$f3->set('can', $can);

		// Latest messages
		$f3->set('latestMessages', $this->_models['message']->latestMessages(5, 600));

		// Boards
		$f3->set('boards', $this->_models['board']->getBoards());
	}

	public function afterRoute($f3)
	{
		if ($f3->get('currentUser')->userID)
		{
			$f3->get('currentUser')->last_active = time();
			$f3->get('currentUser')->save();
		}

		if ($f3->exists('content'))
			echo \Template::instance()->render('home.html');
	}
}
