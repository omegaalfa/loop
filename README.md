# Loop

A simple loop implementation in PHP using Fibers.

## Description

The `Loop` class provides a basic event loop mechanism with support for deferred tasks and timers. It utilizes PHP Fibers to achieve asynchronous execution of tasks.

## Features

- Deferred execution of tasks.
- Timers with specified delays.
- Asynchronous execution using PHP Fibers.

## Requirements

PHP 8.1 or higher.

## Installation

```bash
composer require omegaalfa/loop
```

## Usage

```php
use Omegaalfa\Loop\Loop;

// Create an instance of the EventLoop
$loop = new Loop();

// Add a deferred task
$loop->defer(function () {
    // Code to be executed asynchronously
    echo "Deferred task executed!\n";
});

// Add a timer with a 2-second delay
$loop->setTimeOut(2.0, function () {
    echo "Timer task executed after 2 seconds!\n";
});

// Add a timer with a 1-second delay using addTimer
$loop->addTimer(1.0, function () {
    echo "Timer task executed after 1 second!\n";
});

$loop->repeat(number: 5, intervalSeconds: 2, callback: function () {
    echo "REPEAT\n";
});

// Run the event loop
$loop->run();

```

## Contribution

If you would like to contribute improvements or corrections, feel free to create a pull request or open an issue in the repository.

## License

This project is licensed under the MIT License.
