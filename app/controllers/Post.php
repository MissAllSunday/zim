<?php

namespace Controllers;

class Post extends Base
{
	// Required fields.
	protected $_rows = [];

	function __construct()
	{
		// Need some more goodies.
		$this->_defaultModels[] = 'topic';
		$this->_defaultModels[] = 'board';
		$this->_defaultModels[] = 'allow';

		// Get the default fields.
		$this->_rows = \Models\Message::$rows;
	}

	function post(\Base $f3, $params)
	{
		// The board and topic IDs.
		$this->_rows = array_merge($this->_rows, $params);

		// Check for permissions and that stuff.
		$this->_models['allow']->can('post'. (!empty($this->_rows['topicID']) ? 'Topic' : ''), true);

		// Are we editing? if so, load the data.
		if (!empty($params['edit']));
			$this->_rows = $this->_models['message']->load(['msgID = ?', $params['msgID']]);

		// If theres SESSION data, use that.
		if ($f3->exists('SESSION.posting'))
		{
			$this->_rows = array_merge($this->_rows, $f3->get('SESSION.posting'));

			$f3->clear('SESSION.posting');
		}

		// Check that the board really exists.
		$this->checkBoard($this->_rows['boardID']);

		// If theres a topic ID, make sure it really exists...
		if (!empty($this->_rows['topicID']))
		{
			$this->checkTopic($this->_rows['topicID']);

			$topicInfo = $this->_models['message']->entryInfo($this->_rows['topicID']);

			if (empty($this->_rows['title']))
				$this->_rows['title'] =  $f3->get('txt.re') . $topicInfo['title'];
		}

		// All good.
		$f3->set('posting', $this->_rows);
		$f3->set('quickReply', false);

		// We need these for the editor stuff!
		$f3->push('site.customJS', 'summernote.min.js');
		$f3->push('site.customJS', 'summernote-image-attributes.js');
		$f3->push('site.customCSS', 'summernote.css');

		$f3->set('content','post.html');
		echo \Template::instance()->render('home.html');
	}

	function create(\Base $f3, $params)
	{
		// Need this for those pesky guests!
		$audit = \Audit::instance();

		// Lets end this quick and painless.
		if ($audit->isbot())
			return $f3->reroute('/');

		$errors = [];

		// Captcha.
		if ($f3->get('POST.captcha') != $f3->get('SESSION.captcha_code'))
			$errors[] = 'bad_captcha';

		// Token check.
		if ($f3->get('POST.token')!= $f3->get('SESSION.csrf'))
			$errors[] = 'bad_token';

		$this->_models['message']->reset();

		// Validation and sanitization
		$data = array_map(function($var) use($f3){
			return $f3->get('Tools')->sanitize($var);
		}, array_intersect_key($f3->get('POST'), $this->_rows));

		// Check that the board really exists.
		$this->checkBoard($data['boardID']);

		// If theres a topic ID, make sure it really exists...
		if (!empty($data['topicID']))
			$this->checkTopic($data['topicID']);

		// Check for permissions and that stuff.
		$this->_models['allow']->can('post'. (!empty($data['topicID']) ? 'Topic' : ''), true);

		// Moar handpicked!
		foreach ($data as $k => $v)
			if(empty($v))
				$errors[] = 'empty_'. $k;

		// Did you provide an email? is it valid?
		if (!empty($data['userEmail']) && !$audit->email($data['userEmail']))
			$errors[] = 'bad_email';

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

		// Fill up the user data.
		if ($f3->get('currentUser')->userID)
		{
			$data['userName'] = $f3->get('currentUser')->userName;
			$data['userEmail'] = $f3->get('currentUser')->userEmail;
		}

		$data['userID'] = $f3->get('currentUser')->userID;

		// All good!
		$this->_models['message']->createEntry($data);

		// Get the entry info.
		$topicInfo = $this->_models['message']->entryInfo($this->_models['message']->topicID);

		\Flash::instance()->addMessage($f3->get('txt.post_done'), 'success');

		// Reroute.
		return $f3->reroute('/'. $topicInfo['last_url']);
	}

	function preview(\Base $f3, $params)
	{

	}

	function deleteTopic(\Base $f3, $params)
	{
		// Don't waste my time.
		if (!isset($params['topicID']))
			return $f3->reroute('/');

		// Check for permisisons.
		$this->_models['allow']->can('deleteTopic', true);

		// Check that the topic really does exists.
		$this->checkTopic($params['topicID']);

		// A valid board is also needed.
		// Check that the board really exists.
		$this->checkBoard($params['boardID']);

		// OK then, load the info at once!
		$this->_models['topic']->load(['topicID = ?', $params['topicID']]);

		// Delete all messages associated with this topic.
		$this->_models['topic']->deleteMessages($this->_models['message']->getMessageIDs($params['topicID']));

		// Delete the topic itself.
		$this->_models['topic']->erase();

		// All done, get the board info and lets get the hell out of here!
		$this->_models['board']->load(['boardID = ?', $params['boardID']]);

		return $f3->reroute('/board/'. $this->_models['board']->url);
	}

	function delete(\Base $f3, $params)
	{
		// Check for permisisons.
		$this->_models['allow']->can('delete', true);
	}

	protected function checkBoard($id = 0)
	{
		// Check that the board really exists.
		if (!$this->_models['board']->findone(array('boardID = ?', $id)))
		{
			\Flash::instance()->addMessage('invalid_board', 'danger');
			$this->_models['board']->reset();

			return \Base::instance()->reroute('/');
		}
	}

	protected function checkTopic($id = 0)
	{
		// Check that the topic really exists.
		if (!$this->_models['topic']->findone(array('topicID = ?', $id)))
		{
			\Flash::instance()->addMessage('invalid_topics', 'danger');
			$this->_models['topic']->reset();

			return \Base::instance()->reroute('/');
		}
	}
}
