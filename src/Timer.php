<?php

declare(strict_types=1);

namespace Omegaalfa\Loop;

use Throwable;

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
	 * @throws Throwable
	 */
	public function closure(): void
	{
		$this->setInterval($this->callback, $this->delay);
	}


	/**
	 * @param  callable  $callback
	 * @param  int       $seconds
	 *
	 * @return void
	 * @throws Throwable
	 */
	protected function setInterval(callable $callback, int $seconds): void
	{
		usleep($seconds * 1000000);
		$callback();
	}
}
