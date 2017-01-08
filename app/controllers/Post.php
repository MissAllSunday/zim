<?php

namespace Controllers;

class Post extends Auth
{
	// Required fields.
	protected $_fields = [
		'title' => 'string',
		'body' => 'string',
		'boardID' => 'int',
		'topicID' => 'int',
	];

	function __construct()
	{
		$this->model = new \Models\Blog(\Base::instance()->get('DB'));
		$this->topicModel = new \DB\SQL\Mapper(\Base::instance()->get('DB'),'suki_c_topic');
		$this->boardModel = new \Models\Board(\Base::instance()->get('DB'));
	}

	function post(\Base $f3, $params)
	{
		// If theres SESSION data, use that.
		$f3->mset([
			'post_tags' => ($f3->exists('SESSION.posting.tags') ? $f3->get('SESSION.posting.tags') : ''),
			'post_title' => ($f3->exists('SESSION.posting.title') ? $f3->get('SESSION.posting.title') : ''),
			'post_body' => ($f3->exists('SESSION.posting.body') ? $f3->get('SESSION.posting.body') : ''),
		]);

		// The board and topic IDs are tricky...
		$boardID = ($f3->exists('SESSION.posting.boardID') ? $f3->get('SESSION.posting.boardID') : (!empty($params['boardID']) ? $params['boardID'] : 0));
		$topicID = ($f3->exists('SESSION.posting.topicID') ? $f3->get('SESSION.posting.topic') : (!empty($params['topicID']) ? $params['topicID'] : 0));

		$f3->mset([
			'post_boardID' => $boardID,
			'post_topicID' => $topicID,
		]);

		$f3->clear('SESSION.posting');

		// Check that the board really exists.
		$this->checkBoard($boardID);

		// If theres a topic ID, make sure it really exists...
		if (!empty($topicID))
		{
			$this->checkTopic($topicID);

			$topicInfo = $this->model->entryInfo($topicID);

			if ($f3->get('post_title') == '')
				$f3->set('post_title', $f3->get('txt.re'). $topicInfo['title']);
		}

		// We need these for the editor stuff!
		$f3->push('site.customJS', 'summernote.min.js');
		$f3->push('site.customJS', 'summernote-image-attributes.js');
		$f3->push('site.customCSS', 'summernote.css');

		$f3->set('content','post.html');
		echo \Template::instance()->render('home.html');
	}

	function create(\Base $f3, $params)
	{
		$errors = [];

		$this->model->reset();

		// Validation and sanitization
		$data = array_map(function($var) use($f3){
			return $f3->get('Tools')->sanitize($var);
		}, array_intersect_key($f3->get('POST'), $this->_fields));

		// Token check.
		if ($f3->get('POST.token')!= $f3->get('SESSION.csrf'))
			$errors[] = 'bad_token';

		// Moar handpicked!
		foreach ($data as $k => $v)
			if(empty($v))
				$errors[] = 'empty_'. $k;

		// Clean up the tags.
		$data['tags'] = $f3->exists('POST.tags') ? $f3->get('Tools')->commaSeparated($f3->get('POST.tags')) : '';

		// Lets take five shall we?
		if (!empty($errors))
		{
			// Save the data.
			$f3->set('SESSION.posting', $data);

			\Flash::instance()->addMessage($errors, 'danger');
			return $f3->reroute('/post/'. $data['boardID'] .'/'. $data['topicID']);
		}

		// Check that the board really exists.
		$this->checkBoard($data['boardID']);

		// If theres a topic ID, make sure it really exists...
		if (!empty($data['topicID']))
			$this->checkTopic($data['topicID']);

		// Fill up the user data.
		$data['userID'] = $f3->get('currentUser')->userID;
		$data['userName'] = $f3->get('currentUser')->name;
		$data['userEmail'] = $f3->get('currentUser')->userEmail;

		// All good!
		$this->model->createEntry($data);

		// Get the entry info.
		$topicInfo = $this->model->entryInfo($this->model->topicID);

		\Flash::instance()->addMessage($f3->get('txt.post_done'), 'success');

		// Reroute.
		return $f3->reroute('/'. $topicInfo['url']);
	}

	function preview(\Base $f3, $params)
	{

	}

	protected function checkBoard($id = 0)
	{
		// Check that the board really exists.
		if (!$this->boardModel->findone(array('boardID = ?', $id)))
		{
			\Flash::instance()->addMessage(\Base::instance()->get('txt.invalid_board'), 'danger');
			$this->boardModel->reset();

			return \Base::instance()->reroute('/');
		}
	}

	protected function checkTopic($id = 0)
	{
		// Check that the topic really exists.
		if (!$this->topicModel->findone(array('topicID = ?', $id)))
		{
			\Flash::instance()->addMessage(\Base::instance()->get('txt.invalid_topic'), 'danger');
			$this->topicModel->reset();

			return \Base::instance()->reroute('/');
		}
	}
}
