<?php

namespace Controllers;

class Mods extends Base
{
	protected $client,
		$f3;

	function __construct()
	{
		$f3 = \Base::instance();

		$this->client = new \Github\Client();
		$this->client->authenticate($f3->get('GITHUB.client'), $f3->get('GITHUB.pass'), \Github\Client::AUTH_URL_CLIENT_ID);
	}

	function home(\Base $f3, $params)
	{
		$f3->set('repos', $this->client->api('user')->repositories($f3->get('GITHUB.user')), 86400);
	}
}
