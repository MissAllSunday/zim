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
		$title = $f3->get('txt.signin');
		$f3->set('site', $f3->merge('site', [
			'currentUrl' => $f3->get('URL') .'/login',
		]));
		$f3->set('site.breadcrumb', [
			['url' => $f3->get('site.currentUrl'), 'title' => $title, 'active' => true],
		]);
		$f3->concat('site.metaTitle', $title);
		$f3->set('site.description', $f3->get('txt.signin_desc', $f3->get('site.title')));

		$f3->set('content','login.html');
	}

	function doLogin(\Base $f3, $params)
	{
		// Already logged come on...
		if ($f3->get('currentUser.userID') || \Audit::instance()->isbot())
			return $f3->reroute('/');

		$data = [
			'userEmail' => '',
			'passwd' => '',
			'remember' => 0,
		];

		$error = [];

		// Token check.
		if ($f3->get('POST.token')!= $f3->get('SESSION.csrf'))
			$error[] = 'bad_token';

		// Set the needed vars.
		$data = $f3->clean($f3->get('POST'));

		// Check the POST fields.
		if (empty($data['userEmail']))
			$error[] = 'empty_email';

		if (empty($data['passwd']))
			$error[] = 'empty_password';

		// Need a valid email.
		if (!\Audit::instance()->email($data['userEmail']))
			$error[] = 'bad_email';

		// Get the user's data.
		$found = $this->_models['user']->findone(['userEmail = ?', $data['userEmail']]);

		// No user was found, try again.
		if(empty($found))
			$error[] = 'no_user';

		// Spam check.
		if ($f3->get('Tools')->checkSpam([
			'ip' => $f3->ip(),
			'email' => $data['userEmail'],
		]))
			return $f3->reroute('/');

		// Any errors?
		if ($error)
		{
			\Flash::instance()->addMessage($error, 'danger');
			return $f3->reroute('/login');
		}

		// Do the actual check.
		elseif(password_verify($data['passwd'], $found->passwd))
		{
			// Wanna stay for a bit?
			if (!empty($data['remember']))
				$f3->get('REMEMBER')->setCookie($found->userID);

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
		$f3->get('REMEMBER')->clearCookie($f3->get('currentUser')->userID);
		$f3->clear('currentUser');

		\Flash::instance()->addMessage($f3->get('txt.logout_success'), 'success');
		$f3->reroute('/');
	}

	function signupPage(\Base $f3, $params)
	{
		// Set the needed metatags stuff.
		$title = $f3->get('txt.signup');
		$f3->set('site', $f3->merge('site', [
			'currentUrl' => $f3->get('URL') .'/signup',
		]));
		$f3->set('site.breadcrumb', [
			['url' => $f3->get('site.currentUrl'), 'title' => $title, 'active' => true],
		]);
		$f3->concat('site.metaTitle', $title);
		$f3->set('site.description', $f3->get('txt.signup_desc', $f3->get('site.title')));

		$data = [];

		// If theres SESSION data, use that.
		if ($f3->exists('SESSION.signup'))
		{
			$data = $f3->get('SESSION.signup');

			$f3->clear('SESSION.signup');
		}

		$form = \Form::instance();
		$form->setOptions([
			'action' => 'signup',
			'group' => 'data',
		]);

		foreach ($this->_requiredFields as $v)
			$form->addText([
				'name' => $v,
				'value' => (!empty($data[$v]) ? $data[$v] : ''),
				'text' => $f3->get('txt.login_'. $v),
			]);

		// Avatar
		$form->addRadios([
			'name' => 'avatarType',
			'values' => [
				'identicon' => [$f3->get('txt.login_avatar_generic'), (!empty($data['avatarType']) && $data['avatarType'] == 'identicon' ? true : false)],
				'gravatar' => [$f3->get('txt.login_avatar_gravatar'), (!empty($data['avatarType']) && $data['avatarType'] == 'gravatar' ? true : false)],
				'url' => [$f3->get('txt.login_url'), (!empty($data['avatarType']) && $data['avatarType'] == 'url' ? true : false)]
			],
			'text' => $f3->get('txt.login_avatar'),
			'desc' => $f3->get('txt.login_avatar_desc'),
		]);

		$form->addHiddenField('token', $f3->get('SESSION.csrf'));

		$form->addButton([
			'text' => 'submit',
		]);

		$form->build();

		// Custom JS bits.
		$f3->push('site.customJS', 'signup.js');

		$f3->set('content','form.html');
	}

	function doSignup(\Base $f3, $params)
	{
		$errors = [];

		// Get the needed data.
		$data = $f3->clean($f3->get('POST.data'));

		// Token.
		if (empty($data['token']) || $data['token'] != $f3->get('SESSION.csrf'))
			$errors[] = 'bad_token';

		// Check for empty fields.
		foreach ($this->_requiredFields as $v)
			if(empty($data[$v]))
				$errors[] = 'empty_'. $v;

		// Is there already an user with this email?
		$foundMail = $this->_models['user']->findone(['userEmail = ?', $data['userEmail']]);

		if (!$foundMail)
			$errors[] = 'signup_userName_used';

		// Or perhaps the same userName?
		$foundName = $this->_models['user']->findone(['userName = ?', $data['userName']]);

		if (!$foundName)
			$errors[] = 'signup_userEmail_used';

		// Go back and try again.
		if (!empty($errors))
		{
			// Save the data.
			$f3->set('SESSION.signup', $data);

			\Flash::instance()->addMessage($errors, 'danger');
			return $f3->reroute('/signup');
		}

		// spammer check
		if ($f3->get('Tools')->checkSpam([
			'ip' => $f3->ip(),
			'username' => $data['userName'],
			'email' => $data['userEmail'],
		]))
			return $f3->reroute('/');

		// What kind of avatar do you want to use?
		if (empty($data['avatar']))
		{
			if ($data['avatarType'] == 'gravatar')
				$data['avatar'] = \Gravatar::instance()->get($data['userEmail']);

			else
				$data['avatar'] = $f3->get('BASE') .'/identicon/'. $f3->get('Tools')->slug($data['userName']);
		}

		$refPass = $data['passwd'];
		$data['passwd'] = password_hash($data['passwd'], PASSWORD_DEFAULT);

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
			$f3->get('REMEMBER')->setCookie($this->_models['user']->userID);

			\Flash::instance()->addMessage($f3->get('txt.login_success'), 'success');

			// All done, lets go tell her.
			\Models\Mail::instance()->send([
				'subject' => $f3->get('mail_new_user_subject'),
				'body' => $f3->get('txt.mail_new_user_body', $this->_models['user']->userName)
			]);

			// Send a nice (and quite possible) unwanted welcome email.
			$f3->set('wmail', [
				'userName' => $this->_models['user']->userName,
				'password' => $refPass,
				'link' => $f3->get('site.currentUrl') .'/user/'. $f3->get('Tools')->slug($this->_models['user']->userName) .'-'. $this->_models['user']->userID,
			]);
			$mail = new \Models\Mail;
			$mail->send([
				'html' => true,
				'to' => $this->_models['user']->userEmail,
				'toName' => $this->_models['user']->userName,
				'subject' => $f3->get('txt.mail_welcome', $this->_models['user']->userName),
				'body' => \Template::instance()->render('mail_welcome.html','text/html'),
			]);

			$f3->reroute('/');
		}
	}
}
