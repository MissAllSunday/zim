<?php

namespace Controllers;

class Auth
{
	protected $_models = [];
	protected $_defaultModels = ['message', 'user', 'allow', 'board'];

	function beforeRoute($f3)
	{
		if(empty($f3->get('SESSION.user')))
			$f3->reroute('/login');

			// Gotta stay classy...
			foreach ($this->_defaultModels as $m)
			{
				// ...OK no....
				$class = '\Models\\'. ucfirst($m);
				$this->_models[$m] = new $class($f3->get('DB'));
			}


		// Get current user data.
		$f3->set('currentUser', $this->_models['user']->load(array('userID' => $f3->get('SESSION.user'))));
		$this->_models['user']->reset();

		// Permissions.
		$can = [];

		foreach ($this->_models['allow']->getAll() as $name => $groups)
			$can[$name] = $this->_models['allow']->can($name);

		$f3->set('can', $can);

		// Latest messages
		$f3->set('latestMessages', $this->_models['message']->latestMessages(), 3600);

		// Boards
		$f3->set('boards', $this->_models['board']->getBoards());
	}
}
