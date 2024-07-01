<?php

declare(strict_types=1);

namespace Omegaalfa\Loop;


use Throwable;

trait StreamManagerTrait
{
	/**
	 * @param  resource  $stream
	 * @param  callable  $callback
	 * @param  int       $length
	 * @param  bool      $blocking
	 *
	 * @return void
	 * @throws Throwable
	 */
	protected function streamRead($stream, callable $callback, int $length, bool $blocking = false): void
	{
		stream_set_blocking($stream, $blocking);
		while(!feof($stream)) {
			$read = [$stream];
			$write = null;
			$except = null;

			$ready = stream_select($read, $write, $except, 0, 10000);

			if($ready === false) {
				$this->errors[] = "Error in stream_select";
				fclose($stream);
				return;
			}

			if($ready > 0) {
				$data = fread($stream, max(1, $length));
				if($data !== false) {
					$callback($data);
				}
			}

			$this->next();
		}

		fclose($stream);
	}


	/**
	 * @param  resource  $stream
	 * @param  string    $data
	 * @param  callable  $callback
	 * @param  bool      $blocking
	 *
	 * @return void
	 */
	protected function streamWrite($stream, string $data, callable $callback, bool $blocking = false): void
	{
		$write = [$stream];
		$read = null;
		$except = null;
		$hasWritable = false;
		stream_set_blocking($stream, $blocking);
		while(!$hasWritable) {
			$ready = stream_select($read, $write, $except, 0, 10000);

			if($ready === false) {
				$this->errors[] = "Error in stream_select";
				return;
			}

			if($ready > 0) {
				$hasWritable = true;
			} else {
				usleep(10000);
			}
		}

		fwrite($stream, $data);
		$callback();
		fclose($stream);
	}

	/**
	 * @param  string    $filename
	 * @param  callable  $callback
	 * @param  int       $length
	 * @param  bool      $blocking
	 *
	 * @return void
	 * @throws Throwable
	 */
	protected function streamReadFileNonBlocking(string $filename, callable $callback, int $length, bool $blocking): void
	{
		$stream = fopen($filename, 'rb');
		if(!$stream) {
			$this->errors[] = "Failed to open file: $filename";
			return;
		}

		$this->streamRead($stream, $callback, $length, $blocking);
	}

}
