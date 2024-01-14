<?php

declare(strict_types=1);

namespace omegaalfa\EventLoop;

class Timer
{

	/**
	 * @var int
	 */
	public int $delay;

	/**
	 * @var callable
	 */
	public $callback;

	/**
	 * @param  int       $delay
	 * @param  callable  $callback
	 */
	public function __construct(int $delay, callable $callback)
	{
		$this->delay = $delay;
		$this->callback = $callback;
	}

	/**
	 * @return void
	 */
	public function closure(): void
	{
		$this->setInterval($this->callback, $this->delay);
	}

	/**
	 * @param  float     $seconds
	 * @param  callable  $callback
	 *
	 * @return void
	 */
	public function sleep(float $seconds, callable $callback): void
	{
		$stop = microtime(true) + $seconds;
		for($i = 0; microtime(true) < $stop; $i++) {
			$callback();
		}
	}

	/**
	 * @param  callable  $callback
	 * @param  int       $seconds
	 *
	 * @return void
	 */
	protected function setInterval(callable $callback, int $seconds): void
	{
		sleep($seconds);
		$callback();
	}
}
