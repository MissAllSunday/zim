<?php

namespace Models;

class Auth extends \DB\SQL\Mapper
{
	protected static $expires = 432000;
	protected static $length = 16;
	protected static $cookieName = '';
	protected static $token = '';
	protected static $algo = 'sha256';
	public $f3;

	function __construct(\DB\SQL $db)
	{
		$f3 = \Base::instance();
		self::$cookieName = 'COOKIE.'. md5($f3->get('site.home'));
		self::$token = $this->generateToken();

		parent::__construct($db, $f3->get('_db.prefix') . 'remember');
	}

	function login($remember = false)
	{
		$f3 = \Base::instance();

		// Default stuff.
		$f3->set('currentUser', (object) [
			'userID' => 0,
			'userName' => 'Guest',
			'avatar' => $f3->get('BASE') .'/identicon/'. $f3->get('Tools')->randomString(),
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
				if ((int) $stored->userID)
				{
					$this->setSession($stored->userID);

					// Load the user's data
					$user = new \Models\User($f3->get('DB'));
					$currentUser = $user->findone(['userID = ?', $stored->userID]);

					if ($currentUser)
						$f3->set('currentUser',  $currentUser);

					return true;
				}
			}

			// Fail? fuuuuuuuuuuu
			// Do something, dunno what yet
		}

		return false;
	}

	function setSession($id = 0)
	{
		$f3 = \Base::instance();

		if (!$id)
			return false;

		// Clear up any previous one.
		$this->clearData($id);
		$f3->set('SESSION.user', $id);
	}

	function setCookie($id = 0)
	{
		if (!$id)
			return false;

		$f3 = \Base::instance();
		$selector = $this->generateToken(8);
		$this->reset();
		$this->copyfrom([
			'userID' => $id,
			'token' => hash(self::$algo, self::$token),
			'selector' => $selector,
			'expires' => time() + self::$expires
		]);
		$this->save();

		$f3->set(self::$cookieName, json_encode([
			self::$token,
			$selector,
		]), time() + self::$expires);
	}

	function getCookie()
	{
		$f3 = \Base::instance();
		return $f3->exists(self::$cookieName) ? json_decode($f3->get(self::$cookieName), true) : [];
	}

	function clearData($id = 0)
	{
		$f3 = \Base::instance();
		$f3->clear(self::$cookieName);
		$f3->clear('SESSION');

		if ($id)
			$this->db->exec('DELETE FROM '. $this->table() .' WHERE userID = :user', [':user' => $id]);
	}

	function onSuspect()
	{
		$f3 = \Base::instance();
		$f3->get('REMEMBER')->clearCookie($f3->get('currentUser')->userID);
		$f3->clear('currentUser');

		\Flash::instance()->addMessage($f3->get('txt.logout_success'), 'success');
		$f3->reroute('/');
	}

	function generateToken($length = 0)
	{
		$f3 = \Base::instance();

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
