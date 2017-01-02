<?php

namespace Controllers;

use Services;

class User
{
	function __construct()
	{
		$f3 = \Base::instance();
		$this->model = new \Models\User($f3->get('DB'));
		$this->user = new \Auth($this->model, array('id' => 'name', 'pw' => 'passwd'));
	}

	function loginPage(\Base $f3, $params)
	{
		\Base::instance()->get('Token')->set();

		// Set the needed metatags stuff.

		$f3->set('content','login.html');
		echo \Template::instance()->render('home.html');
	}

	function doLogin(\Base $f3, $params)
	{
		$f3->set('SESSION.loginErrors', null);
		$error = [];

		// Token check.
		if ($f3->get('POST.token') != $f3->get('SESSION.csrf'))
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
		if ($error)
		{
			$f3->set('SESSION.loginErrors', $error);

			$f3->reroute('/login');
		}

		// Do the actual check.
		if(password_verify($passwd, $this->model->passwd))
		{
			$this->user->login($this->model->name, $this->model->passwd);

			$f3->reroute('/');
		}

		else
		{
			$error[] = 'no_user';
			$f3->set('SESSION.loginErrors', $error);

			return $f3->reroute('/login');
		}
	}

	function doLogout()
	{

	}
}
