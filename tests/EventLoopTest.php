<?php

use PHPUnit\Framework\TestCase;
use omegaalfa\EventLoop\EventLoop;

class EventLoopTest extends TestCase
{

	public function testDefer(): void
	{
		$loop = new EventLoop();

		$value = 'Hello, world!';
		$loop->defer(function() use ($value) {
			$this->assertEquals($value, 'Hello, world!');
		});

		$loop->run();
	}

	public function testSetTimeout(): void
	{
		$loop = new EventLoop();

		$value = 'Hello, world!';
		$loop->setTimeout(1, function() use ($value) {
			$this->assertEquals($value, 'Hello, world!');
		});

		$loop->run();
	}

	public function testAddTimer(): void
	{
		$loop = new EventLoop();

		$value = 'Hello, world!';
		$loop->addTimer(1, function() use ($value) {
			$this->assertEquals($value, 'Hello, world!');
		});

		$loop->run();
	}

	public function testSleep(): void
	{
		$loop = new EventLoop();
		$value = 'Hello, world!';
		$loop->defer(function() use ($loop, $value) {
			$loop->sleep(1);
			$this->assertEquals($value, 'Hello, world!');
		});

		$loop->run();
	}

	public function testRun(): void
	{
		$loop = new EventLoop();

		$value = 'Hello, world!';
		$loop->defer(function() use ($value) {
			$this->assertEquals($value, 'Hello, world!');
		});

		$loop->setTimeout(1, function() use ($value) {
			$this->assertEquals($value, 'Hello, world!');
		});

		$loop->run();
	}
}
