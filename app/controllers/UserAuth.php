<?php

namespace Controllers;

class UserAuth extends Base
{
	function __construct()
	{

	}

	function loginPage(\Base $f3, $params)
	{
		// Already logged come on...
		if ($f3->exists('currentUser'))
			return $f3->reroute('/');

		// Set the needed metatags stuff.

		$f3->set('content','login.html');
		echo \Template::instance()->render('home.html');
	}

	function doLogin(\Base $f3, $params)
	{
		// Already logged come on...
		if ($f3->exists('currentUser'))
			return $f3->reroute('/');

		$error = [];

		// Token check.
		if ($f3->get('POST.token')!= $f3->get('SESSION.csrf'))
			$error[] = 'bad_token';

		// Captcha check.
		if ($f3->get('POST.captcha') != $f3->get('SESSION.captcha_code'))
			$error[] = 'bad_captcha';

		// Bot check.
		if (\Audit::instance()->isbot())
			$error[] = 'possible_bot';

		// Set the needed vars.
		$email = $f3->get('POST.email');
		$passwd = $f3->get('POST.password');
		$remember = $f3->get('POST.remember');

		// Check the POST fields.
		if (empty($email))
			$error[] = 'empty_email';

		if (empty($passwd))
			$error[] = 'empty_password';

		// Need a valid email.
		if (!\Audit::instance()->email($email))
			$error[] = 'bad_email';

		// Get the user's data.
		$this->userModel->getByEmail($email);

		// No user was found, try again.
		if($this->userModel->dry())
			$error[] = 'no_user';

		// Any errors?
		if ($error)
		{
			\Flash::instance()->addMessage($error, 'danger');
			return $f3->reroute('/login');
		}

		// Do the actual check.
		elseif(password_verify($passwd, $this->userModel->passwd))
		{
			$f3->set('SESSION.user', $this->userModel->userID);

			// Wanna stay for a bit?
			if (!empty($remember))
			{
				$c = \Bcrypt::instance()->hash($this->userModel->name . $this->userModel->userID);
				$f3->set('COOKIE.'. $c, $c, 60 * 60 * 24 * 7);
			}

			\Flash::instance()->addMessage($f3->get('txt.login_success'), 'success');
			return $f3->reroute('/');
		}

		// Set a default error.
		$error[] = 'no_user';

		\Flash::instance()->addMessage($error, 'danger');
		$f3->reroute('/login');
	}

	function doLogout(\Base $f3, $params)
	{
		$c = \Bcrypt::instance()->hash($this->userModel->name . $this->userModel->userID);

		$f3->clear('COOKIE.'. $c);
		$this->userModel->reset();
		$f3->clear('SESSION');
		\Flash::instance()->addMessage($f3->get('txt.logout_success'), 'success');
		$f3->reroute('/');
	}
}
