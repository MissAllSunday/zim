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
		$cache = \Cache::instance();
		$start = $params['page'] ?: 0;
		$limit = 10;

		if (!$cache->exists('repos'))
		{
			$paginator  = new \Github\ResultPager($this->client);

			$allRepos = $paginator->fetch($this->client->api('user'), 'repositories',[$this->user]);
			$allRepos = array_merge($allRepos, $paginator->fetchNext());
			$cache->set('repos', $allRepos, 86400);
		}

		else
			$allRepos = $cache->get('repos');

		$count = count($allRepos);
		$allRepos = array_chunk($allRepos, $limit);
		$repos = $allRepos[$start];

		$f3->set('repos', $repos);

		$f3->concat('site.metaTitle', $f3->get('txt.goodies_title') . ($start ? $f3->get('txt.page', $start) : ''));

		$pagUrl = $f3->get('URL') . '/goodies'. (!empty($start) ? '/page/'. $start : '');
		$f3->set('site.breadcrumb', [
			['url' => $pagUrl, 'title' => $f3->get('txt.goodies_title')  . ($start ? $f3->get('txt.page', $start) : ''), 'active' => true],
		]);

		$f3->set('pag', [
			'start' => $start,
			'limit' => $limit,
			'pages' => $count / $limit,
			'url' => 'goodies',
		]);

		$f3->set('content','goodies.html');
	}

	function item(\Base $f3, $params)
	{
		$cache = \Cache::instance();

		// Need something to work with
		if (empty($params['item']))
		{
			\Flash::instance()->addMessage($f3->get('txt.goodies_no_item'), 'danger');
			return $f3->reroute('/goodies');
		}

		if (!$cache->exists('repo'. $params['item']))
		{
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
				{
					$repo['commits'][$k]['commit']['message'] = stristr(str_replace(['\n', '\r', '\n\r'], '', $repo['commits'][$k]['commit']['message']), 'Signed-off-by', true);

					$repo['commits'][$k]['commit']['message'] = !empty($repo['commits'][$k]['commit']['message']) ? $repo['commits'][$k]['commit']['message'] : $f3->get('txt.goodies_commit');
				}

			}
			catch (Exception $e)
			{

			}

			$cache->set('repo'. $params['item'], $repo, 86400);
		}

		else
			$repo = $cache->get('repo'. $params['item']);

		$f3->set('repo', $repo);

		$f3->concat('site.metaTitle', $repo['info']['name']);
		$f3->set('site.breadcrumb', [
			['url' => $f3->get('URL') . '/goodies/', 'title' => $f3->get('txt.goodies_title')],
			['url' => $f3->get('URL') . '/goodies/'. $params['item'], 'title' => $repo['info']['name'], 'active' => true],
		]);
		$f3->set('content','goodiesItem.html');
	}
}
