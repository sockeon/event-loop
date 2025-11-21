<?php

declare(strict_types=1);

use Sockeon\EventLoop\Loop\Loop;
use Sockeon\EventLoop\Promise\Deferred;
use Sockeon\EventLoop\Promise\PromiseInterface;

test('Deferred creates a promise', function () {
    $deferred = new Deferred();
    $promise = $deferred->promise();

    expect($promise)->toBeInstanceOf(PromiseInterface::class);
});

test('Deferred resolve fulfills the promise', function () {
    $loop = Loop::getInstance();
    $deferred = new Deferred();
    $value = null;

    $deferred->promise()->then(function ($val) use (&$value, $loop): void {
        $value = $val;
        $loop->stop();
    });

    $deferred->resolve('success');

    $loop->run();

    expect($value)->toBe('success');
});

test('Deferred reject rejects the promise', function () {
    $loop = Loop::getInstance();
    $deferred = new Deferred();
    $reason = null;

    $deferred->promise()->catch(function ($e) use (&$reason, $loop): void {
        $reason = $e;
        $loop->stop();
    });

    $deferred->reject(new RuntimeException('error'));

    $loop->run();

    expect($reason)->toBeInstanceOf(RuntimeException::class);
    expect($reason->getMessage())->toBe('error');
});

test('Deferred resolve throws exception if already resolved', function () {
    $deferred = new Deferred();
    $deferred->resolve('first');

    expect(fn () => $deferred->resolve('second'))->toThrow(RuntimeException::class, 'Promise has already been resolved or rejected');
});

test('Deferred reject throws exception if already rejected', function () {
    $deferred = new Deferred();
    $deferred->reject(new Exception('first'));

    expect(fn () => $deferred->reject(new Exception('second')))->toThrow(RuntimeException::class, 'Promise has already been resolved or rejected');
});

test('Deferred reject throws exception if already resolved', function () {
    $deferred = new Deferred();
    $deferred->resolve('resolved');

    expect(fn () => $deferred->reject(new Exception('error')))->toThrow(RuntimeException::class, 'Promise has already been resolved or rejected');
});

test('Deferred resolve throws exception if already rejected', function () {
    $deferred = new Deferred();
    $deferred->reject(new Exception('rejected'));

    expect(fn () => $deferred->resolve('value'))->toThrow(RuntimeException::class, 'Promise has already been resolved or rejected');
});

test('Deferred can be used with promise chaining', function () {
    $loop = Loop::getInstance();
    $deferred = new Deferred();
    $result = null;

    $deferred
        ->promise()
        ->then(function ($val) {
            return $val * 2;
        })
        ->then(function ($val) use (&$result, $loop): void {
            $result = $val;
            $loop->stop();
        });

    $deferred->resolve(5);

    $loop->run();

    expect($result)->toBe(10);
});

test('Deferred promise can be chained before resolution', function () {
    $loop = Loop::getInstance();
    $deferred = new Deferred();
    $value = null;

    $deferred->promise()->then(function ($val) use (&$value, $loop): void {
        $value = $val;
        $loop->stop();
    });

    // Resolve after chaining
    $deferred->resolve('delayed');

    $loop->run();

    expect($value)->toBe('delayed');
});
