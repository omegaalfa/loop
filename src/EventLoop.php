<?php

namespace omegaalfa\AsyncTask;

use Fiber;
use Throwable;


class EventLoop
{

	/**
	 * @var mixed
	 */
	protected mixed $error = null;

	/**
	 * @var array
	 */
	protected array $timers;

	/**
	 * @param  array  $callables
	 */
	public function __construct(protected array $callables = []) { }

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
	public function setTimeOut(float $seconds, callable $callback): void
	{
		$this->timers[] = new Timer($seconds, $callback);
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
			 call_user_func($callback);
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
	public function next(mixed $value = null): mixed
	{
		return Fiber::suspend($value);
	}

	/**
	 * @return void
	 * @throws Throwable
	 */
	public function run(): void
	{
		if(isset($this->timers) && $this->timers) {
			$this->execTimer();
		}

		$this->execCallables();
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
					$this->error[$key] = $exception->getMessage();
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
		while(!empty($this->callables)) {
			foreach($this->callables as $id => $fiber) {
				try {
					$this->call($id, $fiber);
				} catch(Throwable $exception) {
					$this->error[$id] = $exception->getMessage();
				}
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
				$this->error[$id] = $exception->getMessage();
			}
		}

		unset($this->callables[$id]);

		return $fiber->getReturn();
	}
}
