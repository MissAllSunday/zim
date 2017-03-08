<?php

namespace Models;

class Remember extends \DB\SQL\Mapper
{
	protected static $expires = 604800;
	protected static $length = 16;

	function __construct(\DB\SQL $db)
	{
		$f3 = \Base::instance();

		parent::__construct($db, $f3->get('_db.prefix') . 'remember');
	}

	function login()
	{
		$f3 = \Base::instance();

		// If theres a session, use that.
		if ($f3->exists('SESSION.user'))
			return true;

		$cookie = 'COOKIE.'. md5($f3->get('site.home'));

		if (!$f3->exists($cookie))
			return false;

		$token = $f3->get($cookie);
		$stored = $this->load(['token = ?', $token]);

		if ($this->userID)
			$f3->set('SESSION.user', $this->userID);

		return $this->dry();
	}

	function getUser()
	{
		$f3 = \Base::instance();

		return $f3->exists('SESSION.user') ? $f3->get('SESSION.user') : $this->userID;
	}

	function setCookie($id = 0)
	{
		$f3 = \Base::instance();

		if (!$id)
			return false;

		$token = bin2hex(random_bytes(self::$length));

		$this->reset();
		$this->copyfrom([
			'userID' => $id,
			'token' => $token,
			'expires' => self::$expires
		]);
		$this->save();

		$f3->set('SESSION.user', $id);
		$f3->set('COOKIE.'. md5($f3->get('site.home')), $token, self::$expires);
	}

	function clearCookie($id = 0)
	{
		$f3 = \Base::instance();

		$stored = $this->load(['userID = ?', $id]);
		$cookie = 'COOKIE.'. md5($f3->get('site.home'));

		if ($this->dry() || !$f3->exists($cookie))
			return false;

		$f3->clear($cookie);
		$f3->clear('SESSION');
		$this->db->exec('DELETE FROM '. $this->table() .' WHERE userID = :user', [':user' => $id]);
	}
}
