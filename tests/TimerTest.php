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
		$callback = function() { };
		$timer = new Timer(1, $callback);

		$this->assertEquals($timer->closure(), $callback);
	}

	public function testRepeat(): void
	{
		$callback = function() { };
		$timer = new Timer(1, $callback);

		$this->assertEquals($timer->repeat(1, $callback), $callback);
	}
}
