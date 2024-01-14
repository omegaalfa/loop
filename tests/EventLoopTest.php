<?php

namespace omegaalfa\Tests;

use omegaalfa\EventLoop\EventLoop;
use PHPUnit\Framework\TestCase;

class EventLoopTest extends TestCase
{
    public function testDefer(): void
    {
        $loop = new EventLoop();
        $flag = false;

        $loop->defer(function () use (&$flag) {
            $flag = true;
        });

        $loop->run();

        $this->assertTrue($flag);
    }

    public function testSetTimeOut(): void
    {
        $loop = new EventLoop();
        $flag = false;

        $loop->setTimeOut(1, function () use (&$flag) {
            $flag = true;
        });

        $loop->run();

        $this->assertTrue($flag);
    }

    public function testAddTimer(): void
    {
        $loop = new EventLoop();
        $flag = false;

        $loop->addTimer(1, function () use (&$flag) {
            $flag = true;
        });

        $loop->run();

        $this->assertTrue($flag);
    }

}
