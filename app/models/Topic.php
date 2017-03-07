<?php

namespace Models;

class Topic extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
				parent::__construct($db, \Base::instance()->get('_db.prefix') . 'topic');
	}

	function all()
	{
		$this->load();
		return $this->query;
	}

	function getById($id = 0)
	{
		$this->load(array('topicID = ?', $id));

		return $this->query;
	}
}
