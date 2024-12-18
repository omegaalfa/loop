<?php

declare(strict_types=1);


namespace Omegaalfa\Loop;

class Co
{
	/**
	 * @var Loop
	 */
	protected Loop $loop;

	/**
	 * @var Co|null
	 */
	protected static ?Co $instance = null;


	public function __construct()
	{
		$this->loop = new Loop();
	}

	/**
	 * @return Co
	 */
	public static function getInstance(): Co
	{
		if (self::$instance === null) {
			self::$instance = new Co();
		}

		return self::$instance;
	}


	/**
	 * @param  float|int  $seconds
	 *
	 * @return void
	 */
	public static function sleep(float|int $seconds): void
	{
		try {
			self::getInstance()->loop->sleep($seconds);
		} catch (\Throwable $e) {
			self::getInstance()->loop->defer(fn() => $e->getMessage());
		}
	}


	/**
	 * @param  callable  $callable
	 *
	 * @return void
	 */
	public static function go(callable $callable): void
	{
		self::getInstance()->loop->defer($callable);
	}


	/**
	 * @param  float|int  $seconds
	 * @param  callable   $callback
	 *
	 * @return int
	 */
	public static function addTimer(float|int $seconds, callable $callback): int
	{
		return self::getInstance()->loop->addTimer($seconds, fn() => self::getInstance()->loop->defer($callback));
	}


	/**
	 * @param  float|int  $intervalSeconds
	 * @param  callable   $callback
	 * @param  int|null   $number
	 *
	 * @return void
	 */
	public static function repeat(float|int $intervalSeconds, callable $callback, int|null $number = null): void
	{
		self::getInstance()->loop->repeat($intervalSeconds, fn() => self::getInstance()->loop->defer($callback), $number);
	}

	/**
	 * @param  callable  $callable
	 *
	 * @return void
	 */
	public static function run(callable $callable): void
	{
		$loop = self::getInstance()->loop;
		$loop->defer($callable);
		$loop->run();
	}
}
