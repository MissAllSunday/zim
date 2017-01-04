<?php

namespace Controllers;

class Base
{
	public function beforeRoute($f3)
	{
		// Get current user data. For guest currentUser will be false.
		$this->userModel = new \Models\User($f3->get('DB'));
		$f3->set('currentUser', ($f3->exists('SESSION.user') ? $this->userModel->load(array('userID' => $f3->get('SESSION.user'))) : null));
		$this->userModel->reset();
	}
}
