<?php

declare(strict_types=1);

namespace Omegaalfa\Loop\traits;

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

		while (!feof($stream)) {
			$data = fread($stream, $length);

			if ($data === false) {
				$this->errors[] = "Error in stream_select";
				break;
			}

			if ($data === '') {
				$this->next();
				continue;
			}

			$callback($data);
		}
	}


	/**
	 * @param            $stream
	 * @param  string    $data
	 * @param  callable  $callback
	 * @param  bool      $blocking
	 *
	 * @return void
	 * @throws Throwable
	 */
	protected function streamWrite($stream, string $data, callable $callback, bool $blocking = false): void
	{
		stream_set_blocking($stream, $blocking);

		$length = strlen($data);
		$written = 0;

		while ($written < $length) {
			$result = fwrite($stream, substr($data, $written));

			if ($result === false) {
				break;
			}

			if ($result === 0) {
				$this->next();
				continue;
			}

			$written += $result;
			$callback($written, $length);
		}
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
	protected function streamReadFile(string $filename, callable $callback, int $length, bool $blocking): void
	{
		$handle = fopen($filename, 'rb');

		if ($handle === false) {
			throw new \RuntimeException("Could not open file: $filename");
		}

		try {
			$this->streamRead($handle, $callback, $length, $blocking);
		} finally {
			fclose($handle);
		}
	}
}
