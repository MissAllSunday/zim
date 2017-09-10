<?php

class ConfigTest extends \PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
	}
	public function testSite()
	{
		$f3 = Base::instance();
		$this->assertArrayHasKey('currentUrl', $f3->get('site'));
		$this->assertArrayHasKey('metaTitle', $f3->get('site'));
		$this->assertArrayHasKey('keywords', $f3->get('site'));
		$this->assertArrayHasKey('description', $f3->get('site'));
		$this->assertArrayHasKey('customJS', $f3->get('site'));
		$this->assertArrayHasKey('customExternalJS', $f3->get('site'));
		$this->assertArrayHasKey('customCSS', $f3->get('site'));
	}
}
