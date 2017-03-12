<?php

namespace Models;

class User extends \DB\SQL\Mapper
{
	public $userHref;
	private static $_prefix;

	function __construct(\DB\SQL $db)
	{
		self::$_prefix = \Base::instance()->get('_db.prefix');
		parent::__construct($db, self::$_prefix . 'user');

		$this->isOnline = "last_active >= UNIX_TIMESTAMP() - 300";

		$this->onload(function($self){
			$f3 = \Base::instance();
			$self->userHref = $f3->get('BASE') .'/user/'. $f3->get('Tools')->slug($self->userName) .'-'. $self->userID;
		});
	}

	public function getOnline($params = [], $ttl = 300)
	{
		return $this->find(['userID >= UNIX_TIMESTAMP() - ?', $params['time']], ['limit' => $params['limit']]);
	}

	function loadUsers($users = [])
	{
		$loaded = $data = [];
		$data = $this->find(['userID IN(?)', implode(',', $users)]);

		if (!empty($data))
			foreach ($data as $d)
				$loaded[$d->userID] = $d;

		return $loaded;
	}

	function createUser($data = [])
	{
		$f3 = \Base::instance();
		$this->reset();

		if (empty($data))
			return false;

		// Get what we need
		$data = array_intersect_key($data, array_flip($this->fields()));

		$this->copyfrom($data);

		// Thats pretty much it.
		$this->save();
	}
}
