<?php

namespace Controllers;

class Base
{
	public function beforeRoute($f3)
	{
		// Get current user data. For guest currentUser will be false.
		$model = new \Models\User($f3->get('DB'));
		$f3->set('currentUser', $model->load(array('userID' => $f3->get('SESSION.user'))));

		// Set default metadata tags and/or other common HTML tags.
		$f3->set('site.keywords', $f3->get('txt.site_keywords'));
		$f3->set('site.description', $f3->get('txt.site_desc'));

	}
}
