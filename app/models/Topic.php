<?php

namespace Models;

class Topic extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, 'suki_c_topic');
	}

	public function all()
	{
		$this->load();
		return $this->query;
	}

	public function getById($id = 0)
	{
		$this->load(array('topicID = ?', $id));

		return $this->query;
	}
}
