<?php

namespace Controllers;

class Base
{
	public function beforeRoute($f3)
	{
		// Get current user data. For guest currentUser will be false.
		$model = new \Models\User($f3->get('DB'));
		$f3->set('currentUser', $model->load(array('userID' => $f3->get('SESSION.user'))));
	}
}
