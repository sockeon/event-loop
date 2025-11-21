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

test('Loop methods throw RuntimeException when driver not implemented', function () {
    $loop = Loop::getInstance();

    expect(fn () => $loop->run())->toThrow(RuntimeException::class, 'Event loop driver not yet implemented');
    expect(fn () => $loop->stop())->toThrow(RuntimeException::class, 'Event loop driver not yet implemented');
    expect(fn () => $loop->defer(fn () => null))->toThrow(RuntimeException::class, 'Event loop driver not yet implemented');
    expect(fn () => $loop->delay(1.0, fn () => null))->toThrow(RuntimeException::class, 'Event loop driver not yet implemented');
    expect(fn () => $loop->repeat(1.0, fn () => null))->toThrow(RuntimeException::class, 'Event loop driver not yet implemented');
    expect(fn () => $loop->cancel('test'))->toThrow(RuntimeException::class, 'Event loop driver not yet implemented');

    $stream = fopen('php://memory', 'r+');

    try {
        expect(fn () => $loop->onReadable($stream, fn () => null))->toThrow(RuntimeException::class, 'Event loop driver not yet implemented');
        expect(fn () => $loop->onWritable($stream, fn () => null))->toThrow(RuntimeException::class, 'Event loop driver not yet implemented');
    } finally {
        fclose($stream);
    }
});

test('Loop getInstance is static and returns LoopInterface', function () {
    $loop = Loop::getInstance();

    expect($loop)->toBeInstanceOf(Loop::class);
    expect($loop)->toBeInstanceOf(LoopInterface::class);
});
