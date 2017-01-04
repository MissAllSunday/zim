<?php

namespace Controllers;

class Auth
{
	function beforeRoute($f3)
	{
		if(empty($f3->get('SESSION.user')))
			$f3->reroute('/login');

		// Get current user data.
		$this->userModel = new \Models\User($f3->get('DB'));
		$f3->set('currentUser', ($f3->exists('SESSION.user') ? $this->userModel->load(array('userID' => $f3->get('SESSION.user'))) : null));
		$this->userModel->reset();
	}
}
