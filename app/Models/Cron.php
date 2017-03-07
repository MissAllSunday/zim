<?php

namespace Models;

class Cron extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
				parent::__construct($db, \Base::instance()->get('_db.prefix') . 'cron');
	}
}
