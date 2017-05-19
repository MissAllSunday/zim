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

		$repoInfo = $this->client->api('repo')->show($this->user, $params['item']);

		if (empty($repoInfo))
		{
			\Flash::instance()->addMessage($f3->get('txt.goodies_no_item_found'), 'danger');
				return $f3->reroute('/goodies');
		}

		$apiList = ['contributors', 'languages'];
		$repo = [];

		foreach ($apiList as $name)
		{
			try
			{
				$repo[$name] = $this->client->api('repo')->{$name}($this->user, $params['item']);
			}
			catch(ExceptionA $e)
			{
				// something here, dunno
			}
		}

		$repo['info'] = $repoInfo;

		// Description
		try
		{
			$repo['desc'] = $this->client->api('repo')->contents()->readme($this->user, $params['item']);
			$repo['desc'] = is_array($repo['desc']) && !empty($repo['desc']['content']) ? \Markdown::instance()->convert(base64_decode($repo['desc']['content'])) : $repoInfo['description'];

		}
		catch (Exception $e)
		{
			// something here, dunno
		}

		// Releases
		try
		{
			$repo['releases'] = $this->client->api('repo')->releases()->all($this->user, $params['item']);

			foreach ($repo['releases'] as $k => $r)
				$repo['releases'][$k]['body'] = \Markdown::instance()->convert($r['body']);
		}
		catch (Exception $e)
		{
			// something here, dunno
		}

		// Commits
		try
		{
			$repo['commits'] = $this->client->api('repo')->commits()->all($this->user, $params['item'], ['sha' => $repo['info']['default_branch']]);

			foreach ($repo['commits'] as $k => $v)
				$repo['commits'][$k]['commit']['message'] = stristr(str_replace(['\n', '\r', '\n\r'], '', $repo['commits'][$k]['commit']['message']), 'Signed-off-by', true);

		}
		catch (Exception $e)
		{

		}

		$f3->set('repo', $repo);
		$f3->set('content','goodiesItem.html');
	}

	function search(\Base $f3, $params)
	{

	}
}
