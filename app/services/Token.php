<?php

namespace Services;

class Token
{
	function __construct(\Base $f3)
	{
		$this->f3 = $f3;
	}

	function set()
	{
		$this->ses = new \DB\SQL\Session($this->f3->get('DB'),'token_ses', true, null, 'CSRF');

		// Store it on session.
		$this->f3->copy('CSRF','SESSION.csrf');
	}

	// A simple getter.
	function get()
	{
		return $this->f3->get('SESSION.csrf');
	}
}
