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
