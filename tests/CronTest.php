<?php

class CronTest extends \PHPUnit\Framework\TestCase
{
	protected function setUp()
	{
		$f3 = Base::instance();
		$this->cron = new \Controllers\Cron;
		$this->cron->beforeRoute($f3);
	}

	public function testProperties()
	{
		$this->assertClassHasAttribute('simplePie', '\Controllers\Cron');
		$this->assertInstanceOf('\SimplePie', $this->cron->simplePie);
		$this->assertInstanceOf('\Models\Cron', $this->cron->_models['cron']);
	}
}
