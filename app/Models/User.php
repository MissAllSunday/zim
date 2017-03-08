<?php

namespace Models;

class User extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, \Base::instance()->get('_db.prefix') . 'user');

		$this->isOnline = "last_active >= UNIX_TIMESTAMP() - 300";
	}

	public function getById($id = 0)
	{
		return $this->load(array('userID = ?', $id));
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
			SELECT userID, userName, avatar, avatarType, webUrl, webSite, lmsgID, last_active, (last_active >= UNIX_TIMESTAMP() - 300) AS isOnline
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

	function createUser($data = [])
	{
		$f3 = \Base::instance();

		if (empty($data))
			return false;

		// Get what we need
		$data = array_map(function($var) use($f3){
			return $f3->clean($var);
		}, array_intersect_key(array_flip($this->fields())));

		$this->copyfrom($data);

		// Thats pretty much it.
		$this->save();
	}
}
