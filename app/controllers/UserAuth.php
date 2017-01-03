<?php

namespace Controllers;

class UserAuth extends Base
{
	function __construct()
	{
		$f3 = \Base::instance();
		$this->model = new \Models\User($f3->get('DB'));
	}

	function loginPage(\Base $f3, $params)
	{
		// Set the needed metatags stuff.

		$f3->set('content','login.html');
		echo \Template::instance()->render('home.html');
	}

	function doLogin(\Base $f3, $params)
	{
		$error = [];

		if ($f3->get('POST.token')!= $f3->get('SESSION.csrf'))
			$error[] = 'bad_token';

		// Bot check.
		if (\Audit::instance()->isbot())
			$error[] = 'possible_bot';

		// Set the needed vars.
		$email = $f3->get('POST.email');
		$passwd = $f3->get('POST.password');

		// Check the POST fields.
		if (empty($email))
			$error[] = 'empty_email';

		if (empty($passwd))
			$error[] = 'empty_password';

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
			$f3->set('loginErrors', $error);
			return $f3->reroute('/login');
		}

		// Do the actual check.
		elseif(password_verify($passwd, $this->model->passwd))
		{
			$f3->set('SESSION.user', $this->model->userID);

			\Flash::instance()->addMessage($f3->get('txt.login_success'), 'success');
			return $f3->reroute('/');
		}

		// Set a default error.
		$error[] = 'no_user';

		$f3->set('loginErrors', $error);
		$f3->reroute('/login');
	}

	function doLogout(\Base $f3, $params)
	{
		// Do some other stuff.

		$f3->clear('SESSION');
		\Flash::instance()->addMessage($f3->get('txt.logout_success'), 'success');
		$f3->reroute('/');
	}
}
