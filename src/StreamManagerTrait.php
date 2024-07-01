<?php

declare(strict_types=1);

namespace Omegaalfa\Loop;


use Throwable;

trait StreamManagerTrait
{
	/**
	 * @param            $stream
	 * @param  callable  $callback
	 * @param  int       $length
	 *
	 * @return void
	 * @throws Throwable
	 */
	protected function streamRead($stream, callable $callback, int $length): void
	{
		while(!feof($stream)) {
			$read = [$stream];
			$write = null;
			$except = null;

			$ready = stream_select($read, $write, $except, 0);

			if($ready === false) {
				$this->errors[] = "Error in stream_select";
				fclose($stream);
				return;
			}

			if($ready > 0) {
				$data = fread($stream, $length);
				if($data !== false) {
					$callback($data);
				}
			}

			$this->next();
		}

		fclose($stream);
	}


	/**
	 * @param  resource   $stream
	 * @param  string     $data
	 * @param  callable   $callback
	 * @param  float|int  $timeout
	 *
	 * @return void
	 */
	protected function streamWrite($stream, string $data, callable $callback, float|int $timeout = 0.5): void
	{
		$write = [$stream];
		$read = null;
		$except = null;
		$hasWritable = false;

		while(!$hasWritable) {
			$ready = stream_select($read, $write, $except, 0, $timeout * 1000000);

			if($ready === false) {
				$this->errors[] = "Error in stream_select";
				return;
			}

			if($ready > 0) {
				$hasWritable = true;
			} else {
				usleep($timeout * 1000000);
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
	 *
	 * @return void
	 * @throws Throwable
	 */
	protected function streamReadFileNonBlocking(string $filename, callable $callback, int $length): void
	{
		$file = fopen($filename, 'rb');
		if(!$file) {
			$this->errors[] = "Failed to open file: $filename";
			return;
		}

		stream_set_blocking($file, 0);
		$this->streamRead($file, $callback, $length);
	}

}
