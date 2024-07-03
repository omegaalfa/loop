<?php

declare(strict_types=1);

namespace Omegaalfa\Loop;


use Fiber;
use Throwable;

class Loop
{
	use StreamManagerTrait;

	/**
	 * @var array<int, Fiber<int, mixed, mixed, mixed>>
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
	 * @param  resource  $stream
	 * @param  callable  $callback
	 * @param  bool      $blocking
	 * @param  int       $length
	 *
	 * @return int
	 */
	public function addReadStream($stream, callable $callback, bool $blocking = false, int $length = 8192): int
	{
		return $this->defer(function() use ($length, $stream, $callback, $blocking) {
			$this->streamRead($stream, $callback, $length, $blocking);
		});
	}

	/**
	 * @param  resource  $stream
	 * @param  string    $data
	 * @param  callable  $callback
	 * @param  bool      $blocking
	 *
	 * @return int
	 */
	public function addWriteStream($stream, string $data, callable $callback, bool $blocking = false): int
	{
		return $this->defer(function() use ($stream, $data, $callback, $blocking) {
			$this->streamWrite($stream, $data, $callback, $blocking);
		});
	}

	/**
	 * @param  string    $filename
	 * @param  callable  $callback
	 * @param  bool      $blocking
	 * @param  int       $length
	 *
	 * @return int
	 */
	public function addReadFile(string $filename, callable $callback, bool $blocking = false, int $length = 8192): int
	{
		return $this->defer(function() use ($length, $filename, $callback, $blocking) {
			$this->streamReadFileNonBlocking($filename, $callback, $length, $blocking);
		});
	}

	/**
	 * @param  callable   $callback
	 * @param  float|int  $timeout
	 *
	 * @return int
	 */
	public function setTimeout(callable $callback, float|int $timeout): int
	{
		sleep($timeout);
		return $this->defer(function() use ($callback) {
			try {
				$callback();
			} catch(Throwable $exception) {
				$this->errors[] = $exception->getMessage();
			}
		});
	}

	/**
	 * @param  int        $number
	 * @param  float|int  $intervalSeconds
	 * @param  callable   $callback
	 *
	 * @return int
	 */
	public function repeat(int $number, float|int $intervalSeconds, callable $callback): int
	{
		return $this->defer(function() use ($number, $intervalSeconds, $callback) {
			for($i = 0; $i < $number; ++$i) {
				try {
					$this->sleep($intervalSeconds);
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
	 * @return int
	 */
	public function defer(callable $callable): int
	{
		$fiber = new Fiber($callable);
		$fiberId = spl_object_id($fiber);
		$this->callables[$fiberId] = $fiber;

		return $fiberId;
	}


	/**
	 * @param  float|int  $seconds
	 * @param  callable   $callback
	 *
	 * @return int
	 */
	public function addTimer(float|int $seconds, callable $callback): int
	{
		return $this->defer(function() use ($seconds, $callback) {
			$this->sleep($seconds);
			return $callback();
		});
	}

	/**
	 * @param  float|int  $seconds
	 *
	 * @return void
	 * @throws Throwable
	 */
	public function sleep(float|int $seconds): void
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
	 * @param  int                              $id
	 * @param  Fiber<int, mixed, mixed, mixed>  $fiber
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
		while(!empty($this->callables)) {
			$this->execCallables();
		}
	}
}
