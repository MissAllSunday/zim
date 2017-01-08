<?php

namespace Controllers;

class Cron extends Base
{
	function __construct()
	{
		$f3 = \Base::instance();
		$this->model = new \Models\Cron($f3->get('DB'));
	}

	function init(\Base $f3, $params)
	{

	}
}
