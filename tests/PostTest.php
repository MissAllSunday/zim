<?php

class PostTest extends \PHPUnit_Framework_TestCase
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
