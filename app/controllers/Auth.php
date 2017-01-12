<?php

namespace Controllers;

class Auth
{
	protected $_models = [];
	protected $_defaultModels = ['message', 'user'];

	function beforeRoute($f3)
	{
		// Gotta stay classy...
		foreach ($this->_defaultModels as $m)
		{
			// ...OK no....
			$class = '\Models\\'. ucfirst($m);
			$this->_models[$m] = new $class($f3->get('DB'));
		}

		if(empty($f3->get('SESSION.user')))
			$f3->reroute('/login');

		// Get current user data.
		$f3->set('currentUser', $this->_models['user']->load(array('userID' => $f3->get('SESSION.user'))));
		$this->_models['user']->reset();
	}
}
