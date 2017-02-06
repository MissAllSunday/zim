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

	function loadUsers($users = [])
	{
		$loaded = $data = [];
		$data = $this->db->exec('
			SELECT userID, userName, avatar, avatarType, webUrl, webSite, lmsgID
			FROM suki_c_user AS t
			LEFT JOIN suki_c_message AS m ON (m.msgID = t.fmsgID)
			WHERE userID IN(:users)
			AND is_active = 1', [
			':users' => implode(',', $users),
		]);

		if (!empty($data))
			foreach ($data as $d)
				$loaded[$d['userID']] = $d;

		return $loaded;
	}

	function generateData($user = [])
	{
		$data = [];

		// Set a proper avatar.
		switch ($user['avatarType'])
		{
			case 'value':
				# code...
				break;

			default:
				# code...
				break;
		}

		return $data;
	}
}
