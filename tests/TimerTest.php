<?php

use PHPUnit\Framework\TestCase;
use omegaalfa\EventLoop\EventLoop;
use omegaalfa\EventLoop\Timer;

class TimerTest extends TestCase
{

	public function testConstruct(): void
	{
		$timer = new Timer(1, function() { });

		$this->assertEquals($timer->delay, 1);
		$this->assertEquals($timer->callback, function() {});
	}

	public function testClosure(): void
	{
		$timer = new Timer(1, function () {
			// A lógica do callback aqui...
		});

		$this->expectOutputString('');
		$timer->closure();
	}

	public function testRepeat(): void
	{
		$timer = new Timer(1, function () {
			// A lógica do callback aqui...
		});
		$timer->repeat(1, function () {
			// A lógica do callback aqui...
		});
		$this->expectOutputString('');
	}
}
