<?php

namespace Controllers;

class User extends Base
{
	function profile(\Base $f3, $params)
	{
		// No user? redirect to the main page.
		if (empty($params['user']))
			return $f3->reroute('/');

		// Ugly, I know...
		$user = explode('-', $params['user']);

		// The ID will always be the last key. Since we're here, remove it.
		$id = array_pop($user);

		// Load the user's info.
		$f3->set('user', $this->_models['user']->load(['userID = ?', $id]), 3600);

		// Nothing was found!
		if ($this->_models['user']->dry())
		{
			\Flash::instance()->addMessage('no_user', 'danger');
			$f3->reroute('/');
		}

		$f3->set('content','profile.html');
		echo \Template::instance()->render('home.html');
	}

	function settings(\Base $f3, $params)
	{

	}
}
