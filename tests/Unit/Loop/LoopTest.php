<?php

declare(strict_types=1);

use Sockeon\EventLoop\Loop\Loop;
use Sockeon\EventLoop\Loop\LoopInterface;

test('Loop is a singleton', function () {
    $loop1 = Loop::getInstance();
    $loop2 = Loop::getInstance();

    expect($loop1)->toBe($loop2);
    expect($loop1)->toBeInstanceOf(Loop::class);
});

test('Loop implements LoopInterface', function () {
    $loop = Loop::getInstance();

    expect($loop)->toBeInstanceOf(LoopInterface::class);
});

test('Loop cannot be cloned', function () {
    $loop = Loop::getInstance();

    expect(fn () => clone $loop)->toThrow(Error::class);
});

test('Loop cannot be unserialized', function () {
    $loop = Loop::getInstance();
    $serialized = serialize($loop);

    expect(fn () => unserialize($serialized))->toThrow(RuntimeException::class);
});

test('Loop has all required methods from LoopInterface', function () {
    $loop = Loop::getInstance();
    $reflection = new ReflectionClass($loop);

    expect($reflection->hasMethod('run'))->toBeTrue();
    expect($reflection->hasMethod('stop'))->toBeTrue();
    expect($reflection->hasMethod('defer'))->toBeTrue();
    expect($reflection->hasMethod('delay'))->toBeTrue();
    expect($reflection->hasMethod('repeat'))->toBeTrue();
    expect($reflection->hasMethod('onReadable'))->toBeTrue();
    expect($reflection->hasMethod('onWritable'))->toBeTrue();
    expect($reflection->hasMethod('cancel'))->toBeTrue();
    expect($reflection->hasMethod('getInstance'))->toBeTrue();
});

test('Loop defer schedules callback', function () {
    $loop = Loop::getInstance();
    $executed = false;

    $watcherId = $loop->defer(function () use (&$executed) {
        $executed = true;
    });

    expect($watcherId)->toBeString();
    expect($executed)->toBeFalse();

    $loop->defer(function () use ($loop) {
        $loop->stop();
    });
    $loop->run();

    expect($executed)->toBeTrue();
});

test('Loop delay schedules callback after delay', function () {
    $loop = Loop::getInstance();
    $executed = false;

    $watcherId = $loop->delay(0.01, function () use (&$executed) {
        $executed = true;
    });

    expect($watcherId)->toBeString();
    expect($executed)->toBeFalse();

    $loop->delay(0.02, function () use ($loop) {
        $loop->stop();
    });
    $loop->run();

    expect($executed)->toBeTrue();
});

test('Loop repeat schedules repeating callback', function () {
    $loop = Loop::getInstance();
    $count = 0;

    $watcherId = $loop->repeat(0.01, function () use (&$count, $loop) {
        $count++;
        if ($count >= 3) {
            $loop->stop();
        }
    });

    expect($watcherId)->toBeString();
    expect($count)->toBe(0);

    $loop->run();

    expect($count)->toBe(3);
});

test('Loop cancel removes watcher', function () {
    $loop = Loop::getInstance();
    $executed = false;

    $watcherId = $loop->delay(0.01, function () use (&$executed) {
        $executed = true;
    });

    $loop->cancel($watcherId);

    $loop->delay(0.02, function () use ($loop) {
        $loop->stop();
    });
    $loop->run();

    expect($executed)->toBeFalse();
});

test('Loop onReadable watches stream', function () {
    $loop = Loop::getInstance();
    [$read, $write] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
    $executed = false;

    $watcherId = $loop->onReadable($read, function ($stream) use (&$executed, $loop) {
        $executed = true;
        $loop->stop();
    });

    expect($watcherId)->toBeString();
    expect($executed)->toBeFalse();

    $loop->defer(function () use ($write) {
        fwrite($write, 'test');
    });

    $loop->run();

    expect($executed)->toBeTrue();

    fclose($read);
    fclose($write);
});

test('Loop onWritable watches stream', function () {
    $loop = Loop::getInstance();
    [$read, $write] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
    $executed = false;

    try {
        $watcherId = $loop->onWritable($write, function ($stream) use (&$executed, $loop, $read, $write) {
            $executed = true;
            $loop->stop();
        });

        expect($watcherId)->toBeString();

        $loop->run();

        expect($executed)->toBeTrue();
    } finally {
        if (is_resource($read)) {
            fclose($read);
        }
        if (is_resource($write)) {
            fclose($write);
        }
    }
});

test('Loop stop stops the event loop', function () {
    $loop = Loop::getInstance();
    $executed = false;

    $loop->delay(0.1, function () use (&$executed) {
        $executed = true;
    });

    $loop->defer(function () use ($loop) {
        $loop->stop();
    });

    $start = microtime(true);
    $loop->run();
    $duration = microtime(true) - $start;

    expect($duration)->toBeLessThan(0.05);
    expect($executed)->toBeFalse();
});

test('Loop getInstance is static and returns LoopInterface', function () {
    $loop = Loop::getInstance();

    expect($loop)->toBeInstanceOf(Loop::class);
    expect($loop)->toBeInstanceOf(LoopInterface::class);
});
