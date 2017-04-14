<?php

namespace Models;

class Remember extends \DB\SQL\Mapper
{
	protected static $expires = 432000;
	protected static $length = 16;
	protected $_cookieName = '';
	protected $_token = '';
	public $f3;

	function __construct(\DB\SQL $db)
	{
		$this->f3 = \Base::instance();
		$this->_cookieName = 'COOKIE.'. md5($this->f3->get('site.home'));
		$this->generateToken();

		parent::__construct($db, $this->f3->get('_db.prefix') . 'remember');
	}

	function login($remember = false)
	{
		// If theres a session, use that.
		if ($this->f3->exists('SESSION.user'))
			return true;

		if (!$this->f3->exists($this->_cookieName))
			return false;

		$token = $this->f3->get($this->_cookieName);
		$stored = $this->findone(['token = ?', $token]);

		// User was found, set some new stuff.
		if ($stored->userID)
			$this->setSession($stored->userID, $remember);

		return $stored->userID;
	}

	function setSession($id = 0, $remember = false)
	{
		if (!$id)
			return false;

		// Clear up any previous one.
		$this->clearData($id);

		$this->reset();
		$this->copyfrom([
			'userID' => $id,
			'token' => $this->_token,
			'expires' => ($expires ?: self::$expires)
		]);
		$this->save();

		// Wanna stay for a bit?
		if ($remember)
			$this->setCookie();

		$this->f3->set('SESSION.user', $id);
	}

	function setCookie()
	{
		return $this->f3->set('COOKIE.'. md5($this->f3->get('site.home')), $this->_token, self::$expires);
	}

	function clearData($id = 0)
	{
		$stored = $this->find(['userID = ?', $id]);

		if (empty($stored))
			return false;

		$this->f3->clear($this->_cookieName);
		$this->f3->clear('SESSION');
		$this->db->exec('DELETE FROM '. $this->table() .' WHERE userID = :user', [':user' => $id]);
	}

	function onSuspect()
	{
		$this->f3->get('REMEMBER')->clearCookie($this->f3->get('currentUser')->userID);
		$this->f3->clear('currentUser');

		\Flash::instance()->addMessage($this->f3->get('txt.logout_success'), 'success');
		$this->f3->reroute('/');
	}

	function generateToken()
	{
		// Should catch the Exception but meh
		return $this->$_token = bin2hex(random_bytes(self::$length));
	}

	function guest($data = [])
	{

	}
}
