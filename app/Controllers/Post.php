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

		// Get the default fields.
		$this->_rows = array_merge(\Models\Message::$rows, \Models\Message::$topicRows);
	}

	function post(\Base $f3, $params)
	{
		// The board and topic IDs.
		$this->_rows = array_merge($this->_rows, $params);
		$f3->set('isEditing', strpos($params[0], 'edit') !== false);

		// Check for permissions and that stuff.
		$this->_models['allow']->can('post'. (empty($this->_rows['topicID']) ? 'Topic' : ''), true);

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

		// Are we editing? if so, load the data.
		if ($f3->get('isEditing') && !empty($params['msgID']))
		{
			$this->_models['message']->reset();
			$this->_models['message']->locked = $topicInfo['locked'];
			$this->_models['message']->sticky = $topicInfo['sticky'];
			$this->_rows = $this->_models['message']->load(['msgID = ?', $params['msgID']]);
			$f3->set('isTopic', ($this->_rows['msgID'] == $topicInfo['msgID']));
		}

		// Check for locked status.
		if (!$f3->get('isEditing') && $topicInfo['locked'])
			return $f3->reroute('/'. $topicInfo['pagUrl']);

		$this->_models['board']->load(['boardID = ?', $this->_rows['boardID']]);

		// The title one is tricky
		$title = !$this->_rows['title'] ? $f3->get('txt.newtopic') : ($f3->get('isEditing') ? $f3->get('txt.post_editing', $this->_rows['title']) : $f3->get('txt.post_replyto', $topicInfo['title']));

		$f3->concat('site.metaTitle', $title);

		$f3->set('site.breadcrumb', [
			['url' => 'board/'. $this->_models['board']->url, 'title' => $this->_models['board']->title],
			['url' => '', 'title' => $title, 'active' => true],
		]);

		// All good.
		$f3->set('posting', $this->_rows);
		$f3->set('quickReply', false);

		// We need these for the editor stuff!
		if ($f3->get('currentUser')->userID)
		{
			$f3->push('site.customJS', 'summernote.min.js');
			$f3->push('site.customJS', 'summernote-image-attributes.js');
			$f3->push('site.customCSS', 'summernote.css');
		}

		$f3->set('content','post.html');
	}

	function create(\Base $f3, $params)
	{
		$toCheck = ['title', 'body'];

		$f3->set('isEditing', strpos($params[0], 'edit') !== false);

		// Guest need some more checks
		if (!$f3->get('currentUser')->userID)
		{
			$toCheck[] = 'userEmail';
			$toCheck[] = 'userName';
		}

		// Need this for those pesky guests!
		$audit = \Audit::instance();

		// Lets end this quick and painless.
		if ($audit->isbot())
			return $f3->reroute('/');

		$errors = [];

		// Token check.
		if ($f3->get('POST.token')!= $f3->get('SESSION.csrf'))
			$errors[] = 'bad_token';

		$this->_models['message']->reset();

		// Validation
		$data = array_intersect_key($f3->get('POST'), $this->_rows);

		// Check that the board really exists.
		$this->checkBoard($data['boardID']);

		// If theres a topic ID, make sure it really exists...
		if (!empty($data['topicID']))
			$this->checkTopic($data['topicID']);

		// Check for permissions and that stuff.
		$this->_models['allow']->can('post'. (empty($data['topicID']) ? 'Topic' : ''), true);

		// Moar handpicked!
		foreach ($toCheck as $v)
			if(empty($data[$v]))
				$errors[] = 'empty_'. $v;

		// Did you provide an email? is it valid?
		if (!empty($data['userEmail']) && !$audit->email($data['userEmail'], true))
			$errors[] = 'bad_email';

		// Guest posting? check that they aren't using an already registered user/mail
		if (!$f3->get('currentUser')->userID)
		{
			// Before doing that, lets see if you're a naughty spammer...
			if ($f3->get('Tools')->checkSpam([
				'ip' => $f3->ip(),
				'username' => $data['userName'],
				'email' => $data['userEmail'],
			]))
				return $f3->reroute('/');

			$found = $this->_models['user']->find(['userName = ? OR userEmail = ?', $data['userName'], $data['userEmail']]);

			if(!empty($found))
				$errors[] = 'already_used';
		}

		// Clean up the tags.
		$data['tags'] = !empty($data['tags']) ? $f3->get('Tools')->commaSeparated($data['tags']) : '';

		// Lets take five shall we?
		if (!empty($errors))
		{
			// Save the data.
			$f3->set('SESSION.posting', array_map(function($var) use($f3){
				return $f3->get('Tools')->sanitize($var);
			}, array_intersect_key($f3->get('POST'), $this->_rows)));

			\Flash::instance()->addMessage($errors, 'danger');
			return $f3->reroute('/post/'. $data['boardID'] .'/'. $data['topicID']);
		}

		// This is here because plain textareas can't handle br tags.
		// Guest posting hackish hack is hackish...
		if (!$f3->get('currentUser')->userID)
			$data['body'] = nl2br($data['body'], false);

		// Are we editing? if so, load the data.
		if ($f3->get('isEditing') && !empty($data['msgID']))
		{
			$this->_models['message']->reset();
			$editedData = $this->_models['message']->load(['msgID = ?', $data['msgID']]);
			$f3->set('isTopic', ($editedData['msgID'] == $topicInfo['msgID']));
			$data = array_merge($data, [
				'userName' => $editedData->userName,
				'userEmail' => $editedData->userEmail,
				'userID' => $editedData->userID,
			]);
		}

		// topic/reply then, fill up the user stuff from the current user
		elseif ($f3->get('currentUser')->userID)
			$data = array_merge($data, [
				'userName' => $f3->get('currentUser')->userName,
				'userEmail' => $f3->get('currentUser')->userEmail,
				'userID' => $f3->get('currentUser')->userID,
			]);

		// All good!
		$this->_models['message']->createEntry($data);

		// Get the entry info.
		$topicInfo = $this->_models['message']->entryInfo($this->_models['message']->topicID);

		\Flash::instance()->addMessage($f3->get('txt.post_done', $f3->get('txt.post_action_'. (!empty($data['msgID']) ? 'edited' : 'posted'))), 'success');

		// Reroute.
		return $f3->reroute('/'. $topicInfo['lurl']);
	}

	function preview(\Base $f3, $params)
	{

	}

	function deleteTopic(\Base $f3, $params)
	{
		// Don't waste my time.
		if (!isset($params['topicID']))
			return $f3->reroute('/');

		// Check for permissions.
		$this->_models['allow']->can('deleteTopic', true);

		// Check that the topic really does exists.
		$this->checkTopic($params['topicID']);

		// A valid board is also needed.
		$this->checkBoard($params['boardID']);

		// OK then, load the info at once!
		$this->_models['topic']->load(['topicID = ?', $params['topicID']]);

		// Delete all messages associated with this topic.
		$this->_models['message']->deleteMessages($this->_models['message']->getMessageIDs($params['topicID']));

		// Delete the topic itself.
		$this->_models['topic']->erase();

		// All done, get the board info and lets get the hell out of here!
		$this->_models['board']->load(['boardID = ?', $params['boardID']]);

		\Flash::instance()->addMessage($f3->get('txt.post_done', $f3->get('txt.post_action_deleted')), 'success');

		return $f3->reroute('/board/'. $this->_models['board']->url);
	}

	function delete(\Base $f3, $params)
	{
		// Don't waste my time.
		if (!isset($params['msgID']))
			return $f3->reroute('/');

		// Check for permissions.
		$this->_models['allow']->can('delete', true);

		// Check that the topic really does exists.
		$this->checkTopic($params['topicID']);

		// A valid board is also needed.
		$this->checkBoard($params['boardID']);

		$this->_models['message']->load(['msgID = ?', $params['msgID']]);

		// Does it exists?
		if ($this->_models['message']->dry())
		{
			\Flash::instance()->addMessage($f3->get('txt.error_invalid_message'), 'danger');

			return $f3->reroute('/');
		}

		// Perform.
		$this->_models['message']->erase();

		// Update the number of replies.
		$this->_models['topic']->updateNumReplies($params['topicID']);

		// Get the topic info and be done already.
		$entryInfo = $this->_models['message']->entryInfo($params['topicID']);

		\Flash::instance()->addMessage($f3->get('txt.post_done', $f3->get('txt.post_action_deleted')), 'success');

		return $f3->reroute('/'. $entryInfo['last_url']);
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
