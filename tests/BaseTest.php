<?php

class BaseTest extends \PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
	}

	public function testProperties()
	{
		$f3 = Base::instance();
		$this->assertClassHasAttribute('_models', '\Controllers\Base');
		$this->assertClassHasAttribute('_defaultModels', '\Controllers\Base');
	}

	public function testSite()
	{
		$f3 = Base::instance();
		$base = new \Controllers\Base;
		$base->beforeRoute($f3);

		$this->assertArrayHasKey('currentUrl', $f3->get('site'));
		$this->assertArrayHasKey('metaTitle', $f3->get('site'));
		$this->assertArrayHasKey('keywords', $f3->get('site'));
		$this->assertArrayHasKey('description', $f3->get('site'));
		$this->assertArrayHasKey('customJS', $f3->get('site'));
		$this->assertArrayHasKey('customExternalJS', $f3->get('site'));
		$this->assertArrayHasKey('customCSS', $f3->get('site'));
	}

	public function testModels()
	{
		$f3 = Base::instance();
		$base = new \Controllers\Base;
		$base->beforeRoute($f3);

		$this->assertInstanceOf('\Controllers\Base', $base);

		foreach ($base->_models as $m)
		{
			$name = get_class($m);
			$expected = $name;
			$this->assertInstanceOf($expected, $base->_models[strtolower(substr($name, strrpos($name, '\\') + 1))]);
		}
	}
}
