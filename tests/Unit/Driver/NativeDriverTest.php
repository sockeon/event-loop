<?php

declare(strict_types=1);

use Sockeon\EventLoop\Driver\DriverInterface;
use Sockeon\EventLoop\Driver\NativeDriver;

test('NativeDriver implements DriverInterface', function () {
    $driver = new NativeDriver();

    expect($driver)->toBeInstanceOf(DriverInterface::class);
});

test('NativeDriver can defer callbacks', function () {
    $driver = new NativeDriver();
    $executed = false;

    $id = $driver->defer(function () use (&$executed) {
        $executed = true;
    });

    expect($id)->toBeString();
    expect($executed)->toBeFalse();

    // Run the loop briefly
    $driver->defer(function () use ($driver) {
        $driver->stop();
    });

    $start = microtime(true);
    $driver->run();
    $duration = microtime(true) - $start;

    expect($executed)->toBeTrue();
    expect($duration)->toBeLessThan(0.1); // Should complete quickly
});

test('NativeDriver can schedule delayed callbacks', function () {
    $driver = new NativeDriver();
    $executed = false;

    $id = $driver->delay(0.1, function () use (&$executed) {
        $executed = true;
    });

    expect($id)->toBeString();

    $driver->delay(0.15, function () use ($driver) {
        $driver->stop();
    });

    $start = microtime(true);
    $driver->run();
    $duration = microtime(true) - $start;

    expect($executed)->toBeTrue();
    expect($duration)->toBeGreaterThan(0.09);
    expect($duration)->toBeLessThan(0.2);
});

test('NativeDriver can schedule repeating callbacks', function () {
    $driver = new NativeDriver();
    $count = 0;

    $id = $driver->repeat(0.05, function () use (&$count, $driver) {
        $count++;
        if ($count >= 3) {
            $driver->stop();
        }
    });

    expect($id)->toBeString();

    $start = microtime(true);
    $driver->run();
    $duration = microtime(true) - $start;

    expect($count)->toBe(3);
    expect($duration)->toBeGreaterThan(0.1);
    expect($duration)->toBeLessThan(0.3);
});

test('NativeDriver can watch readable streams', function () {
    $driver = new NativeDriver();
    $executed = false;

    // Use a pipe to ensure the stream is actually readable
    [$readStream, $writeStream] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
    
    // Write data to make it readable
    fwrite($writeStream, 'test');
    fclose($writeStream);

    $id = $driver->onReadable($readStream, function ($s) use (&$executed, $driver) {
        $executed = true;
        $driver->stop();
    });

    expect($id)->toBeString();

    $driver->run();

    expect($executed)->toBeTrue();
    fclose($readStream);
});

test('NativeDriver can watch writable streams', function () {
    $driver = new NativeDriver();
    $executed = false;

    // Use a pipe - the write stream should be writable
    [$readStream, $writeStream] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);

    $id = $driver->onWritable($writeStream, function ($s) use (&$executed, $driver) {
        $executed = true;
        $driver->stop();
    });

    expect($id)->toBeString();

    $driver->run();

    expect($executed)->toBeTrue();
    fclose($readStream);
    fclose($writeStream);
});

test('NativeDriver can cancel watchers', function () {
    $driver = new NativeDriver();
    $executed = false;

    $id = $driver->defer(function () use (&$executed) {
        $executed = true;
    });

    $driver->cancel($id);

    $driver->defer(function () use ($driver) {
        $driver->stop();
    });

    $driver->run();

    expect($executed)->toBeFalse();
});

test('NativeDriver throws exception for negative delay', function () {
    $driver = new NativeDriver();

    expect(fn() => $driver->delay(-1.0, fn() => null))->toThrow(InvalidArgumentException::class);
});

test('NativeDriver throws exception for negative interval', function () {
    $driver = new NativeDriver();

    expect(fn() => $driver->repeat(-1.0, fn() => null))->toThrow(InvalidArgumentException::class);
});

test('NativeDriver throws exception for invalid stream in onReadable', function () {
    $driver = new NativeDriver();

    expect(fn() => $driver->onReadable('not a stream', fn() => null))->toThrow(InvalidArgumentException::class);
});

test('NativeDriver throws exception for invalid stream in onWritable', function () {
    $driver = new NativeDriver();

    expect(fn() => $driver->onWritable('not a stream', fn() => null))->toThrow(InvalidArgumentException::class);
});

test('NativeDriver throws exception when run is called while already running', function () {
    $driver = new NativeDriver();

    $driver->defer(function () use ($driver) {
        expect(fn() => $driver->run())->toThrow(RuntimeException::class, 'Event loop is already running');
        $driver->stop();
    });

    $driver->run();
});

test('NativeDriver can stop the event loop', function () {
    $driver = new NativeDriver();
    $executed = false;

    $driver->defer(function () use (&$executed, $driver) {
        $executed = true;
        $driver->stop();
    });

    $driver->run();

    expect($executed)->toBeTrue();
});

