<?php

namespace Controllers;

class Auth
{
	function beforeRoute($f3)
	{
		if(empty($f3->get('SESSION.user')))
			$f3->reroute('/login');

		// Get current user data.
		$model = new \Models\User($f3->get('DB'));
		$f3->set('currentUser', $model->load(array('userID' => $f3->get('SESSION.user'))));

		// Set default metadata tags and/or other common HTML tags.
	}
}
