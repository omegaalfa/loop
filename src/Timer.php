<?php

declare(strict_types=1);

namespace Omegaalfa\Loop;


use Throwable;

class Timer
{

	/**
	 * @var int|float
	 */
	public int|float $delay;

	/**
	 * @var callable
	 */
	public $callback;

	/**
	 * @param  int|float  $delay
	 * @param  callable   $callback
	 */
	public function __construct(int|float $delay, callable $callback)
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
	 * @param  callable   $callback
	 * @param  int|float  $seconds
	 *
	 * @return void
	 */
	protected function setInterval(callable $callback, int|float $seconds): void
	{
		$microsegunds = (int)$seconds * 1000000;
		usleep($microsegunds);
		$callback();
	}
}
