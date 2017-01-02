<?php

namespace Controllers;

class Auth
{
	function beforeRoute($f3)
	{
		if(empty($f3->get('SESSION.user')))
			$f3->reRoute('/login');
	}
}
