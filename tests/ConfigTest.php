<?php

class ConfigTest extends \PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
	}
	public function testGet()
	{
		$f3 = Base::instance();
		$this->assertNull($f3->mock('GET /path'));
	}
}
