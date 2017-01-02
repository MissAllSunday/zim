<?php

namespace Controllers;

use Services;

class User
{
	function __construct()
	{
		$this->model = new \Models\User(\Base::instance()->get('DB'));

		\Base::instance()->get('Token')->set();
		$this->userSes = new \Auth($this->model, array('id' => 'name', 'pw' => 'passwd'));
	}

	function loginPage()
	{
		// Set the needed metatags stuff.

		echo \Template::instance()->render('loginPage.html');
	}

	function login(\Base $f3, $params)
	{
		$error = [];

		// Token check.
		if ($f3->get('POST.token')!= $f3->get('Token')->set())
			$error[] = 'bad_token';

		// Bot check.
		if (\Audit::instance()->isbot())
			$error[] = 'possible_bot';

		// Check the POST fields.
		if (!$f3->exists('POST.email'))
			$error[] = 'empty_email';

		if (!$f3->exists('POST.password'))
			$error[] = 'empty_password';

		// Set the needed vars.
		$email = $f3->get('POST.email');
		$passwd = $f3->get('POST.password');

		// Need a valid email.
		if (!\Audit::instance()->email($email))
			$error[] = 'bad_email';

		// Get the user's data.
		$this->model->getByEmail($email);

		// No user was found, try again.
		if($this->model->dry())
			$error[] = 'no_user';

		// Any errors?
		if ($errors)
		{
			$f3->set('SESSION.loginErrors', $errors);

			return $f3->reroute('/login');
		}

		// Do the actual check.
		if(password_verify($passwd, $this->model->passwd))
		{
			$this->userSes->login($this->model->name, $this->model->passwd);

			return $f3->reroute('/');
		}

		else
		{
			$error[] = 'no_user';
			$f3->set('SESSION.loginErrors', $errors);

			return $f3->reroute('/login');
		}
	}

	function doLogOut()
	{

	}
}
