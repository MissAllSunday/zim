<?php

namespace Controllers;

class Auth
{
	function beforeRoute($f3)
	{
		if(empty($f3->get('SESSION.user')))
			$f3->reroute('/login');

		// Get current user data.

		// Set default metadata tags and/or other common HTML tags.
	}
}
