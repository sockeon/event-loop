<?php

declare(strict_types=1);

use Sockeon\EventLoop\EventLoop;

test('event loop can be instantiated', function () {
    $loop = new EventLoop();
    expect($loop)->toBeInstanceOf(EventLoop::class);
});

