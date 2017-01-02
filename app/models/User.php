<?php

namespace Models;

class User extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, 'suki_c_user');
	}

	public function all()
	{
		$this->load();
		return $this->query;
	}

	public function getById($id = 0)
	{
		$this->load(array('userID = ?', $id));

		return $this->query;
	}

	public function getByName($name = 0)
	{
		return $this->load(array('name = ?', $name));
	}

	public function getByEmail($email = '')
	{
		return $this->load(array('userEmail = ?', $email));
	}
}
