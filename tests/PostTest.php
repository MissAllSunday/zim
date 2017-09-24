<?php

class PostTest extends \PHPUnit\Framework\TestCase
{
	protected function setUp()
	{
	}

	public function testProperties()
	{
		$f3 = Base::instance();
		$this->assertClassHasAttribute('_rows', '\Controllers\Post');
	}
}
