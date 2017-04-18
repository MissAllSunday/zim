<?php

namespace Models;

class Remember extends \DB\SQL\Mapper
{
	protected static $expires = 432000;
	protected static $length = 16;
	protected static $cookieName = '';
	protected static $token = '';
	protected static $algo = 'sha256';
	public $f3;

	function __construct(\DB\SQL $db)
	{
		$this->f3 = \Base::instance();
		self::$cookieName = 'COOKIE.'. md5($this->f3->get('site.home'));
		self::$token = $this->generateToken();

		parent::__construct($db, $this->f3->get('_db.prefix') . 'remember');
	}

	function login($remember = false)
	{
		// Default stuff.
		$this->f3->set('currentUser', [
			'userID' => 0,
			'userName' => 'Guest',
			'userEmail' => '',
			'avatar' => $this->f3->get('BASE') .'/identicon/'. $this->f3->get('Tools')->randomString(),
			'groupID' => 0,
			'groups' => '',
			'lmsgID' => 0,
			'isBot' => \Audit::instance()->isbot(),
		]);

		$cookieData = $this->getCookie();

		if (empty($cookieData))
			return;

		$stored = $this->findone(['selector = ?', $cookieData[1]]);

		// User was found, do some checks.
		if (!empty($stored))
		{
			if (hash_equals(hash(self::$algo, $cookieData[0]), $stored->token))
			{
				// User?
				if ((int) $stored->userID > 0 && !$stored->data)
				{
					$this->setSession($stored->userID, $remember);

					// Load the user's data
					$user = new \Models\User;
					$currentUser = $user->findone(['userID = ?', $stored->userID]);

					// The user doesn't exists anymore, show an error message or something...
				}

				elseif ($stored->data)
					$currentUser = array_merge($currentUser, json_decode($stored->data, true));

				if ($currentUser)
					$this->f3->set('currentUser', $this->f3->merge('currentUser', $currentUser));

				return true;
			}

			// Fail? fuuuuuuuuuuu
			// Do something, dunno what yet
		}

		return false;
	}

	function setSession($id = 0)
	{
		if (!$id)
			return false;

		// Clear up any previous one.
		$this->clearData($id);
		$this->f3->set('SESSION.user', $id);
	}

	function setCookie()
	{
		$selector = $this->generateToken(8);
		$this->reset();
		$this->copyfrom([
			'userID' => $id,
			'token' => hash(self::$algo, self::$token),
			'selector' => $selector,
			'expires' => time() + self::$expires
		]);
		$this->save();

		$this->f3->set($this->_cookieName, json_encode([
			self::$token,
			$selector,
		]), time() + self::$expires);
	}

	function getCookie()
	{
		return $this->f3->exists(self::$cookieName) ? json_decode($this->f3->get(self::$cookieName), true) : [];
	}

	function clearData($id = 0)
	{
		$this->f3->clear($this->_cookieName);
		$this->f3->clear('SESSION');

		if ($id)
			$this->db->exec('DELETE FROM '. $this->table() .' WHERE userID = :user', [':user' => $id]);
	}

	function onSuspect()
	{
		$this->f3->get('REMEMBER')->clearCookie($this->f3->get('currentUser')->userID);
		$this->f3->clear('currentUser');

		\Flash::instance()->addMessage($this->f3->get('txt.logout_success'), 'success');
		$this->f3->reroute('/');
	}

	function generateToken($length = 0)
	{
		// Should catch the Exception but meh
		return bin2hex(random_bytes($length ?: self::$length));
	}

	function generate($pass = '')
	{
		return password_hash(
			$pass,
			PASSWORD_DEFAULT
		);
	}

	function verify($pass, $found)
	{
		return password_verify(
			$pass,
			$found
		);
	}
}
