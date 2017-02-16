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
		if ($f3->get('currentUser.userID') || \Audit::instance()->isbot())
			return $f3->reroute('/');

		// Set the needed metatags stuff.

		$f3->set('content','login.html');
		echo \Template::instance()->render('home.html');
	}

	function doLogin(\Base $f3, $params)
	{
		// Already logged come on...
		if ($f3->get('currentUser.userID') || \Audit::instance()->isbot())
			return $f3->reroute('/');

		$data = [
			'email' => '',
			'passwd' => '',
			'remember' => 0,
		];

		$error = [];

		// Token check.
		if ($f3->get('POST.token')!= $f3->get('SESSION.csrf'))
			$error[] = 'bad_token';

		// Captcha check.
		if ($f3->get('POST.captcha') != $f3->get('SESSION.captcha_code'))
			$error[] = 'bad_captcha';

		// Set the needed vars.
		$data = array_intersect_key($f3->clean($f3->get('POST')), $data);

		// Check the POST fields.
		if (empty($data['email']))
			$error[] = 'empty_email';

		if (empty($data['passwd']))
			$error[] = 'empty_password';

		// Need a valid email.
		if (!\Audit::instance()->email($data['email']))
			$error[] = 'bad_email';

		// Get the user's data.
		$this->_models['user']->getByEmail($data['email']);

		// No user was found, try again.
		if($this->_models['user']->dry())
			$error[] = 'no_user';

		// Any errors?
		if ($error)
		{
			\Flash::instance()->addMessage($error, 'danger');
			return $f3->reroute('/login');
		}

		// Do the actual check.
		elseif(password_verify($data['passwd'], $this->_models['user']->passwd))
		{
			$f3->set('SESSION.user', $this->_models['user']->userID);

			// Wanna stay for a bit?
			if (!empty($data['remember']))
			{
				$f3->set('COOKIE.'. md5($f3->get('site.title')), $this->_models['user']->userID, 60 * 60 * 24 * 7);
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
		$f3->clear('COOKIE.'. md5($f3->get('site.title')));
		$this->_models['user']->reset();
		$f3->clear('SESSION');
		\Flash::instance()->addMessage($f3->get('txt.logout_success'), 'success');
		$f3->reroute('/');
	}

	function signupPage(\Base $f3, $params)
	{
		$fields = [
			'userName',
			'userEmail',
			'passwd',
		];
		$form = \Form::instance();
		$form->setOptions([
			'prefix' => 'sign_',
			'action' => 'signup',
		]);

		foreach ($fields as $f)
			$form->addText([
				'name' => $f,
				'value' => '',
				'text' => $f3->get('txt.login_'. $f),
			]);

		$form->addButton([
			'text' => 'submit',
		]);

		$form->build();

		$f3->set('content','form.html');
		echo \Template::instance()->render('home.html');
	}
}
