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
		$this->logger = new \Log('mailError.log');

		// Get the credentials.
		$this->_options = $this->f3->get('EMAIL');

		$this->init();
	}

	function init($options = [])
	{
		// Allow using this with different credentials.
		if (!empty($options))
			$this->_options = array_merge($this->_options, $options);

		$this->mail = new PHPMailer(true);
		$this->mail->isSMTP();
		$this->mail->Host = $this->_options['host'];
		$this->mail->SMTPAuth = true;
		$this->mail->Username = $this->_options['userName'];
		$this->mail->Password = $this->_options['password'];
		$this->mail->SMTPSecure = 'ssl';
		$this->mail->Port = $this->_options['port'];
		$this->mail->setFrom($this->_options['from'], 'Info');
	}

	function send($data = [])
	{
		// Need something to work with.
		if (empty($data))
			return false;

		try
		{
			$this->mail->isHTML(false);
			$this->mail->addAddress(!empty($data['to']) ? $data['to'] : $this->f3->get('EMAIL.to');
			$this->mail->Subject = $data['subject'];
			$this->mail->Body = $data['body'];

			// To infinity... and beyond!!!
			if(!$this->mail->send())
				$this->logger->write($this->mail->ErrorInfo);
		}

		catch (phpmailerException $e)
		{
			$this->logger->write($e->errorMessage());
		}
		catch (Exception $e)
		{
			$this->logger->write($e->getMessage());
		}
	}
}
