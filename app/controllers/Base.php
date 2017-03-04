<?php

namespace Controllers;

class Base
{
	protected $_models = [];
	protected $_defaultModels = ['message', 'user', 'allow', 'board'];

	public function beforeRoute($f3)
	{
		// Gotta stay classy...
		foreach ($this->_defaultModels as $m)
		{
			// ...OK no....
			$class = '\Models\\'. ucfirst($m);
			$this->_models[$m] = new $class($f3->get('DB'));
		}

		// Get current user data.
		if ($f3->exists('SESSION.user'))
			$currentUser = $this->_models['user']->load(array('userID' => $f3->get('SESSION.user')));

		else
			$currentUser = (object) [
				'userID' => 0,
				'userName' => 'Guest',
				'avatar' => $f3->get('BASE') .'/identicon/'. $f3->get('Tools')->randomString(),
				'groupID' => 0,
				'groups' => '',
				'lmsgID' => 0,
			];

		$currentUser->isBot = \Audit::instance()->isbot();

		$f3->set('currentUser', $currentUser);
		$this->_models['user']->reset();

		// Permissions.
		$can = [];

		foreach ($this->_models['allow']->getAll() as $name => $groups)
			$can[$name] = $this->_models['allow']->can($name);

		$f3->set('can', $can);

		// Latest messages
		$f3->set('latestMessages', $this->_models['message']->latestMessages());

		// Boards
		$f3->set('boards', $this->_models['board']->getBoards());
	}

	public function afterRoute($f3)
	{
		if ($f3->get('currentUser')->userID && $this->_models['user']->userID)
		{
			$f3->get('currentUser')->last_active = time();
			$f3->get('currentUser')->save();
		}
	}
}
