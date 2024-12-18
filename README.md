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
## Usage class wrapper for loop

```php
Co::run(function() {
    Co::go(function() {
        Co::sleep(3);
        echo 'ROCKS' . PHP_EOL;
    });
    Co::go(function() {
        Co::sleep(2);
        echo 'CAFE' . PHP_EOL;
    });
    Co::go(function() {
        Co::sleep(1);
        echo 'PHP' . PHP_EOL;
    });
    Co::addTimer(1.5, function() {
        echo 'Add timer' . PHP_EOL;
    });

    Co::repeat(1, function() {
        echo 'repeat' . PHP_EOL;
    }, 3);

    Co::go(function() {
        for($i = 0; $i < 10; $i++) {
            echo "Counting: $i\n";
            Co::sleep(0.5);
        }
    });
```

## Output

```php
Counting: 0
Counting: 1
PHP
Counting: 2
repeat
Add timer
Counting: 3
CAFE
repeat
Counting: 4
Counting: 5
ROCKS
repeat
Counting: 6
Counting: 7
Counting: 8
Counting: 9

```
## Contribution

If you would like to contribute improvements or corrections, feel free to create a pull request or open an issue in the repository.

## License

This project is licensed under the MIT License.