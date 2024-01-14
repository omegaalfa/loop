# EventLoop

A simple event loop implementation in PHP using Fibers.

## Description

The `EventLoop` class provides a basic event loop mechanism with support for deferred tasks and timers. It utilizes PHP Fibers to achieve asynchronous execution of tasks.

## Features

- Deferred execution of tasks.
- Timers with specified delays.
- Asynchronous execution using PHP Fibers.

## Requirements

PHP 8.1 or higher.

## Installation

Instructions on how to install the package.

## Usage

```php
use omegaalfa\Event\EventLoop;

// Create an instance of the EventLoop
$eventLoop = new EventLoop();

// Add a deferred task
$eventLoop->defer(function () {
    // Code to be executed asynchronously
    echo "Deferred task executed!\n";
});

// Add a timer with a 2-second delay
$eventLoop->setTimeOut(2.0, function () {
    echo "Timer task executed after 2 seconds!\n";
});

// Add a timer with a 1-second delay using addTimer
$eventLoop->addTimer(1.0, function () {
    echo "Timer task executed after 1 second!\n";
});

// Run the event loop
$eventLoop->run();

```

## Contribuição

Se desejar contribuir com melhorias ou correções, fique à vontade para criar uma pull request ou abrir uma issue no repositório.

## Licença

Este projeto está licenciado sob a Licença MIT.
