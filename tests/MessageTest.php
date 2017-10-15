<?php

class MessageTest extends \PHPUnit\Framework\TestCase
{
	protected function setUp()
	{
		$f3 = Base::instance();

		$this->message = new \Models\Message($f3->get('DB'));

		// random body is random
		$randomLines = $f3->get('txt.randomLines');

		// Create a dummy Topic
		$this->message->createEntry(array_merge(\Models\Message::$rows, [
			'msgID' => 0,
			'msgTime' => time(),
			'boardID' => 1,
			'topicID' => 0,
			'approved' => 1,
			'userIP' => $f3->ip(),
			'title' => 'topic test',
			'body' => $randomLines[mt_rand(0, 20)],
		]));

		// Keep the topic Id reference
		$this->topicReference = $message->topicID;

		// and a message too!
		$this->message->createEntry(array_merge(\Models\Message::$rows, [
			'msgID' => 0,
			'msgTime' => time(),
			'boardID' => 1,
			'topicID' => $message->topicID,
			'approved' => 1,
			'userIP' => $f3->ip(),
			'body' => $randomLines[mt_rand(0, 20)],
		]));
	}

	public function testProperties()
	{
		$this->assertClassHasAttribute('_rows', '\Controllers\Post');
	}

	public function testCreateEntry()
	{
		$f3 = Base::instance();

		// No params, no business
		$this->assertFalse($this->message->createEntry([]));

		$this->message->createEntry(array_merge(\Models\Message::$rows, [
			'msgID' => 0,
			'msgTime' => time(),
			'boardID' => 1,
			'topicID' => $this->topicReference,
			'approved' => 1,
			'userIP' => $f3->ip(),
			'body' => 'lol',
		]));

		$this->assertTrue((bool) $this->message->msgID);
		$this->assertTrue((bool) $this->message->topicID);
		$this->assertEquals('lol', $this->message->body);
		$this->assertInternalType('string', $this->message->userName);
	}

	public function testEntries()
	{
		$f3 = Base::instance();
		$start = 0;
		$entries = $this->message->entries([
			':limit' => $f3->get('paginationLimit'),
			':start' => $start * $f3->get('paginationLimit'),
			':board' => 1
		]);

		$this->assertInternalType('array', $entries);
		$this->assertContainsOnly('array', $entries);

		foreach($entries as $k => $entry)
		{
			$this->assertArrayHasKey('msgID', $entry);
			$this->assertArrayHasKey('body', $entry);
			$this->assertTrue(is_string($entry['body']));
			$this->assertInternalType('string', $entry['userName']);
		}
	}
}
