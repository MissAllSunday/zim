<?php

namespace Models;

class Mail extends \Prefab
{
	protected $_options = [],
		$logger,
		$mail,
		$f3;

	function __construct()
	{
		$this->f3 = \Base::instance();

		// Get the credentials.
		$this->_options = $this->f3->get('EMAIL');

		$this->init();
	}

	function init($options = [])
	{
		// Allow using this with different credentials.
		if (!empty($options))
			$this->_options = array_merge($this->_options, $options);

		$this->mail = new \SMTP($this->_options['host'], $this->_options['port'], 'ssl', $this->_options['userName'], $this->_options['password']);
	}

	function send($data = [])
	{
		// Need something to work with.
		if (empty($data))
			return false;

		$data['to'] = (!empty($data['to']) ? $data['to'] : $this->f3->get('EMAIL.to'));

		$data['from'] = (!empty($data['from']) ? $data['from'] : $this->f3->get('EMAIL.from'));

		$this->mail->set('Content-Type','text/html; charset="UTF-8"');
		$this->mail->set('From', $data['from']);
		$this->mail->set('To', $data['to']);
		$this->mail->set('Subject', $data['subject']);

		return $this->mail->send($data['body'], true);
	}
}
