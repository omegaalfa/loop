<?php

use PHPUnit\Framework\TestCase;
use Omegaalfa\Loop\Loop;

class EventLoopTest extends TestCase
{

	protected Loop $loop;

	protected function setUp(): void
	{
		$this->loop = new Loop();
	}


	public function testSetTimeout(): void
	{
		$called = false;
		$this->loop->setTimeout(function() use (&$called) {
			$called = true;
		}, 0.1);

		$this->loop->run();
		$this->assertTrue($called);
	}

	public function testRepeat(): void
	{
		$counter = 0;
		$this->loop->repeat(0.1, function() use (&$counter) {
			$counter++;
		}, 3);

		$this->loop->run();
		$this->assertEquals(3, $counter);
	}

	public function testDefer(): void
	{
		$called = false;
		$this->loop->defer(function() use (&$called) {
			$called = true;
		});

		$this->loop->run();
		$this->assertTrue($called);
	}

	public function testAddTimer(): void
	{
		$called = false;
		$this->loop->addTimer(0.1, function() use (&$called) {
			$called = true;
		});

		$this->loop->run();
		$this->assertTrue($called);
	}
	

	public function testCancel(): void
	{
		$called = false;
		$id = $this->loop->defer(function() use (&$called) {
			$called = true;
		});

		$this->loop->cancel($id);
		$this->loop->run();

		$this->assertFalse($called);
	}
}
