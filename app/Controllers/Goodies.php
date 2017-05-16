<?php

namespace Controllers;

class Goodies extends Base
{
	protected $client,
		$user,
		$f3;

	function __construct()
	{
		$f3 = \Base::instance();
		$this->user = $f3->get('GITHUB.user');

		$this->client = new \Github\Client();
		$this->client->authenticate($f3->get('GITHUB.client'), $f3->get('GITHUB.pass'), \Github\Client::AUTH_URL_CLIENT_ID);
	}

	function home(\Base $f3, $params)
	{
		$paginator  = new \Github\ResultPager($this->client);
		$f3->set('repos', (!empty($params['page']) ? $paginator->fetchNext() : $paginator->fetch($this->client->api('user'), 'repositories', [$this->user])));

		$f3->concat('site.metaTitle', $f3->get('txt.goodies_title'));

		$f3->set('site.breadcrumb', [
			['url' => $f3->get('URL') . '/goodies/', 'title' => $f3->get('txt.goodies_title'), 'active' => true],
		]);

		$f3->set('content','goodies.html');
	}

	function item(\Base $f3, $params)
	{
		// Need something to work with
		if (empty($params['item']))
		{
			\Flash::instance()->addMessage($f3->get('txt.goodies_no_item'), 'danger');
			return $f3->reroute('/goodies');
		}

		$repo = $this->client->api('repo')->show($this->user, $params['item']);

		if (empty($repo))
		{
			\Flash::instance()->addMessage($f3->get('txt.goodies_no_item_found'), 'danger');
				return $f3->reroute('/goodies');
		}

		$readMe = $this->client->api('repo')->contents()->readme($this->user, $params['item']);

		$readMe = is_array($readMe) && !empty($readMe['content']) ? \Markdown::instance()->convert(base64_decode($readMe['content'])) : $repo['description'];

		$f3->set('repoDesc', $readMe);

		$releases = $this->client->api('repo')->releases()->all($this->user, $params['item']);

		if (is_array($releases))
			foreach ($releases as $k => $r)
				$releases[$k]['body'] = \Markdown::instance()->convert($r['body']);

		$f3->set('repoReleases', !empty($releases) ? $releases : $f3->get('txt.goodies_no_releases'));

		$f3->set('content','goodiesItem.html');
	}

	function search(\Base $f3, $params)
	{

	}
}
