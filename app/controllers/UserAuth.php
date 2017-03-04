<?php

namespace Controllers;

class UserAuth extends Base
{
	protected $_requiredFields = [
		'userName',
		'userEmail',
		'passwd',
	];

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
		$form = \Form::instance();
		$form->setOptions([
			'action' => 'signup',
			'group' => 'data',
		]);

		foreach ($this->_requiredFields as $f)
			$form->addText([
				'name' => $f,
				'value' => '',
				'text' => $f3->get('txt.login_'. $f),
			]);

		// Avatar
		$form->addRadios([
			'name' => 'avatarType',
			'values' => [
				'identicon' => [$f3->get('txt.login_avatar_generic'), true],
				'gravatar' => [$f3->get('txt.login_avatar_gravatar')],
				'url' => [$f3->get('txt.login_url')]
			],
			'text' => $f3->get('txt.login_avatar'),
			'desc' => $f3->get('txt.login_avatar_desc'),
		]);

		$form->addCaptcha([
			'name' => 'captcha',
			'url' => 'captcha',
			'text' => $f3->get('txt.login_captcha'),
		]);

		$form->addHiddenField('token', $f3->get('SESSION.csrf'));

		$form->addButton([
			'text' => 'submit',
		]);

		$form->build();

		// Custom JS bits.
		$f3->push('site.customJS', 'signup.js');

		$f3->set('content','form.html');
		echo \Template::instance()->render('home.html');
	}

	function doSignup(\Base $f3, $params)
	{
		$errors = [];

		// Get the needed data.
		$data = array_intersect_key($f3->clean($f3->get('POST')), $this->_requiredFields);

		// Captcha.
		if ($f3->exists('POST.captcha') && $f3->get('POST.captcha') != $f3->get('SESSION.captcha_code'))
			$errors[] = 'bad_captcha';

		// Check for empty fields.
		foreach ($this->_requiredFields as $v)
			if(empty($data[$v]))
				$errors[] = 'empty_'. $v;

		// Is there already an user with this email?
		$this->_models['user']->findone(['userEmail = ?', $data['userEmail']]);

		if (!$this->_models['user']->dry())
			$errors[] = 'signup_userName_used';

		// Or perhaps the same userName?
		$this->_models['user']->findone(['userName = ?', $data['userName']]);

		if (!$this->_models['user']->dry())
			$errors[] = 'signup_userEmail_used';

		$this->_models['user']->reset();

		// Avatar stuff is not required but needs to be included anyway.
		$data['avatar'] = $f3->clean($f3->get('POST.avatar'));
		$data['avatarType'] = $f3->clean($f3->get('POST.avatarType'));

		// Go back and try again.
		if (!empty($errors))
		{
			// Save the data.
			$f3->set('SESSION.signup', $data);

			\Flash::instance()->addMessage($errors, 'danger');
			return $f3->reroute('/signup');
		}

		// What kind of avatar do you want to use?
		if (empty($data['avatar']))
		{
			if ($data['avatarType'] == 'gravatar')
				$data['avatar'] = \Gravatar::instance()->get($data['userEmail']);

			$data['avatar'] = $f3->get('BASE') .'/identicon/'. $data['userName'];
		}

		$data['passwd'] = password_hash($data['passwd']);

		// Lets fill up some things.
		$this->_models['user']->createUser(array_merge([
			'userIP' => $f3->ip(),
			'title' => '',
			'registered' => time(),
			'posts' => 0,
			'groupID' => 0,
			'groups' => '',
			'lastLogin' => time(),
			'webUrl' => '',
			'webSite' => '',
			'passwdSalt	' => '',
			'lmsgID' => 0,
			'is_active' => 1,
		], $data));

		// User was created, set the cookie
		if($this->_models['user']->userID)
		{
			$f3->set('SESSION.user', $this->_models['user']->userID);

			$f3->set('COOKIE.'. md5($f3->get('site.title')), $this->_models['user']->userID, 60 * 60 * 24 * 7);

			\Flash::instance()->addMessage($f3->get('txt.login_success'), 'success');

			// All done, lets go tell her.
			\Models\Mail::instance()->send([
				'subject' => $f3->get('mail_new_user_subject'),
				'body' => $f3->get('mail_new_user_body', $this->_models['user']->userName)
			]);

			$f3->reroute('/');
		}
	}
}
