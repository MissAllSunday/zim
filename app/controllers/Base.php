<?php

namespace Controllers;

class Base
{
	protected $_models = [];
	protected $_defaultModels = ['message', 'user'];

	public function beforeRoute($f3)
	{
		// Gotta stay classy...
		foreach ($this->_defaultModels as $m)
		{
			// ...OK no....
			$class = '\Models\\'. ucfirst($m);
			$this->_models[$m] = new $class($f3->get('DB'));
		}

		// Get current user data. For guest currentUser will be false.
		$f3->set('currentUser', ($f3->exists('SESSION.user') ? $this->_models['user']->load(array('userID' => $f3->get('SESSION.user'))) : [
			'userID' => 0,
			'userName' => 'guest',
		]));
		$this->_models['user']->reset();
	}
}
