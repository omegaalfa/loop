<?php

declare(strict_types=1);

namespace Omegaalfa\Loop;


use Fiber;
use Throwable;

class Loop
{
	use StreamManagerTrait;

	/**
	 * @var array<int, Fiber>
	 */
	protected array $callables = [];

	/**
	 * @var array<int, Timer>
	 */
	protected array $timers = [];

	/**
	 * @var array<int, string>
	 */
	protected array $errors = [];

	/**
	 * @var array<int, resource>
	 */
	protected array $readStreams = [];

	/**
	 * @var array<int, resource>
	 */
	protected array $writeStreams = [];

	/**
	 * @param  resource  $stream
	 * @param  callable  $callback
	 * @param  int       $length
	 *
	 * @return void
	 */
	public function addReadStream($stream, callable $callback, int $length = 8192): void
	{
		$this->readStreams[(int)$stream] = $stream;
		$this->defer(function() use ($length, $stream, $callback) {
			$this->streamRead($stream, $callback, $length);
		});
	}

	/**
	 * @param             $stream
	 * @param  string     $data
	 * @param  callable   $callback
	 * @param  float|int  $timeout
	 *
	 * @return void
	 */
	public function addWriteStream($stream, string $data, callable $callback, float|int $timeout = 0.5): void
	{
		$this->writeStreams[(int)$stream] = $stream;
		$this->defer(function() use ($timeout, $stream, $data, $callback) {
			$this->streamWrite($stream, $data, $callback, $timeout);
		});
	}

	/**
	 * @param  string    $filename
	 * @param  callable  $callback
	 * @param  int       $length
	 *
	 * @return void
	 */
	public function addReadFile(string $filename, callable $callback, int $length = 8192): void
	{
		$this->defer(function() use ($length, $filename, $callback) {
			$this->streamReadFileNonBlocking($filename, $callback, $length);
		});
	}

	/**
	 * @param  callable  $cb
	 * @param  float     $timeout
	 *
	 * @return void
	 */
	public function setTimeout(callable $cb, float $timeout): void
	{
		$triggerTime = microtime(true) + $timeout;
		$this->timers[] = [$triggerTime, $cb];
		usort($this->timers, static fn($a, $b) => $a[0] <=> $b[0]);
	}

	/**
	 * @param  float     $seconds
	 * @param  callable  $callback
	 *
	 * @return void
	 * @throws Throwable
	 */
	public function repeat(float $seconds, callable $callback): void
	{
		$this->defer(function() use ($seconds, $callback) {
			while(true) {
				$this->sleep($seconds);
				try {
					$callback();
				} catch(Throwable $exception) {
					$this->errors[] = $exception->getMessage();
				}
			}
		});
	}

	/**
	 * @param  callable  $callable
	 *
	 * @return void
	 */
	public function defer(callable $callable): void
	{
		$this->callables[] = new Fiber($callable);
	}


	/**
	 * @param  float     $seconds
	 * @param  callable  $callback
	 *
	 * @return void
	 */
	public function addTimer(float $seconds, callable $callback): void
	{
		$this->defer(function() use ($seconds, $callback) {
			$this->sleep($seconds);
			return $callback();
		});
	}

	/**
	 * @param  float  $seconds
	 *
	 * @return void
	 * @throws Throwable
	 */
	public function sleep(float $seconds): void
	{
		$stop = microtime(true) + $seconds;
		for($i = 0; microtime(true) < $stop; $i++) {
			$this->next();
		}
	}

	/**
	 * @param  mixed|null  $value
	 *
	 * @return mixed
	 * @throws Throwable
	 */
	protected function next(mixed $value = null): mixed
	{
		return Fiber::suspend($value);
	}

	/**
	 * @param  int  $id
	 *
	 * @return void
	 */
	public function cancel(int $id): void
	{
		if(isset($this->callables[$id])) {
			unset($this->callables[$id]);
		}
	}

	/**
	 * @param  int  $id
	 *
	 * @return void
	 */
	public function cancelTimer(int $id): void
	{
		if(isset($this->timers[$id])) {
			unset($this->timers[$id]);
		}
	}

	/**
	 * @return void
	 */
	private function execTimer(): void
	{
		$now = microtime(true);
		foreach($this->timers as $key => $timer) {
			if($timer->delay <= $now) {
				try {
					$timer->closure();
				} catch(Throwable $exception) {
					$this->errors[$key] = $exception->getMessage();
				}

				unset($this->timers[$key]);
			}
		}
	}

	/**
	 * @return void
	 */
	private function execCallables(): void
	{
		foreach($this->callables as $id => $fiber) {
			try {
				$this->call($id, $fiber);
			} catch(Throwable $exception) {
				$this->errors[$id] = $exception->getMessage();
			}
		}
	}

	/**
	 * @param  int    $id
	 * @param  Fiber  $fiber
	 *
	 * @return mixed
	 * @throws Throwable
	 */
	protected function call(int $id, Fiber $fiber): mixed
	{
		if(!$fiber->isStarted()) {
			return $fiber->start($id);
		}

		if(!$fiber->isTerminated()) {
			try {
				return $fiber->resume();
			} catch(Throwable $exception) {
				$this->errors[$id] = $exception->getMessage();
			}
		}

		unset($this->callables[$id]);

		return $fiber->getReturn();
	}


	/**
	 * @return void
	 */
	public function run(): void
	{
		while(!empty($this->callables) || !empty($this->timers)) {
			if(!empty($this->timers)) {
				$this->execTimer();
			}

			$this->execCallables();
		}
	}
}
