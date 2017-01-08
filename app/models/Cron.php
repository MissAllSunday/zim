<?php

namespace Models;

class Cron
{
	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, 'suki_c_cron');
	}
}
