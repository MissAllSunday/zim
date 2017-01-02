<?php

namespace Controllers;

use Services;

class User
{
	function __construct()
	{
		$this->model = new \Models\User(\Base::instance()->get('DB'));

		$f3->get('Token')->set();
	}

	function loginPage
	{
		// Set the needed metatags stuff.

		echo \Template::instance()->render('loginPage.html');
	}

	function login(\Base $f3, $params)
	{
		// Token check.
		if ($f3->get('POST.token')!= $f3->get('Token')->set())
		{

		}

		$email = $f3->get('POST.email');
		$password = $f3->get('POST.password');

		// Check the POST fields.

		// Get the user's data.
		$this->model->getByEmail($email);

		// No user was found, try again.
		if($user->dry())
			return $this->f3->reroute('/login');

		// All good!
		if(password_verify($password, $this->model->passwd))
		{

		}

		// Nope! try again.
		else
		{

		}
	}
}
