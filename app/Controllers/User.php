<?php

namespace Controllers;

class User extends Base
{
	function __construct()
	{
		// Need a few extra things.
		$this->_defaultModels[] = 'topic';
	}
	function profile(\Base $f3, $params)
	{
		// No user? redirect to the main page.
		if (empty($params['user']))
			return $f3->reroute('/');

		// Ugly, I know...
		$user = explode('-', $params['user']);

		// The ID will always be the last key. Since we're here, remove it.
		$id = array_pop($user);

		// Load the user's info.
		$f3->set('user', $this->_models['user']->load(['userID = ?', $id]));

		// Nothing was found!
		if ($this->_models['user']->dry())
		{
			\Flash::instance()->addMessage('no_user', 'danger');
			$f3->reroute('/');
		}

		// Latest topics.
		$f3->set('profileTopics', $this->_models['topic']->getByUser([
			':user' => $this->_models['user']->userID,
			':limit' => 10
		]));

		// Latest messages.
		$f3->set('profileMessages', $this->_models['message']->userMessages([
			':user' => $id,
			':limit' => 10,
		]));

		$title = $f3->get('txt.view_profile', $this->_models['user']->userName);
		$f3->set('site', $f3->merge('site', [
			'currentUrl' => $this->_models['user']->userHref,
		]));
		$f3->set('site.breadcrumb', [
			['url' => $f3->get('site.currentUrl'), 'title' => $title, 'active' => true],
		]);

		$f3->concat('site.metaTitle', $title);
		$f3->set('site.description', $f3->get('site.metaTitle'));

		$f3->set('content','profile.html');
	}

	function settings(\Base $f3, $params)
	{

	}
}
