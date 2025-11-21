<?php

declare(strict_types=1);

use Sockeon\EventLoop\Loop\Loop;
use Sockeon\EventLoop\Promise\Promise;
use Sockeon\EventLoop\Promise\PromiseInterface;

test('Promise implements PromiseInterface', function () {
    $promise = new Promise(function ($resolve): void {
        $resolve('test');
    });

    expect($promise)->toBeInstanceOf(PromiseInterface::class);
});

test('Promise resolves with value', function () {
    $loop = Loop::getInstance();
    $value = null;

    $promise = new Promise(function ($resolve): void {
        $resolve('success');
    });

    $promise->then(function ($val) use (&$value, $loop): void {
        $value = $val;
        $loop->stop();
    });

    $loop->run();

    expect($value)->toBe('success');
});

test('Promise rejects with exception', function () {
    $loop = Loop::getInstance();
    $reason = null;

    $promise = new Promise(function ($resolve, $reject): void {
        $reject(new RuntimeException('error'));
    });

    $promise->catch(function ($e) use (&$reason, $loop): void {
        $reason = $e;
        $loop->stop();
    });

    $loop->run();

    expect($reason)->toBeInstanceOf(RuntimeException::class);
    expect($reason->getMessage())->toBe('error');
});

test('Promise chains with then', function () {
    $loop = Loop::getInstance();
    $result = null;

    $promise = new Promise(function ($resolve): void {
        $resolve(1);
    });

    $promise
        ->then(function ($val) {
            return $val + 1;
        })
        ->then(function ($val) {
            return $val * 2;
        })
        ->then(function ($val) use (&$result, $loop): void {
            $result = $val;
            $loop->stop();
        });

    $loop->run();

    expect($result)->toBe(4);
});

test('Promise catch handles errors', function () {
    $loop = Loop::getInstance();
    $error = null;

    $promise = new Promise(function ($resolve, $reject): void {
        $reject(new Exception('test error'));
    });

    $promise
        ->then(function () {
            return 'should not run';
        })
        ->catch(function ($e) use (&$error, $loop): void {
            $error = $e;
            $loop->stop();
        });

    $loop->run();

    expect($error)->toBeInstanceOf(Exception::class);
    expect($error->getMessage())->toBe('test error');
});

test('Promise finally always executes', function () {
    $loop = Loop::getInstance();
    $finallyCalled = false;
    $value = null;

    $promise = new Promise(function ($resolve): void {
        $resolve('success');
    });

    $promise
        ->then(function ($val) use (&$value): string {
            $value = $val;

            return $val;
        })
        ->finally(function () use (&$finallyCalled, $loop): void {
            $finallyCalled = true;
            $loop->stop();
        });

    $loop->run();

    expect($finallyCalled)->toBeTrue();
    expect($value)->toBe('success');
});

test('Promise finally executes on rejection', function () {
    $loop = Loop::getInstance();
    $finallyCalled = false;
    $error = null;

    $promise = new Promise(function ($resolve, $reject): void {
        $reject(new Exception('error'));
    });

    $promise
        ->catch(function ($e) use (&$error): void {
            $error = $e;

            throw $e;
        })
        ->finally(function () use (&$finallyCalled, $loop): void {
            $finallyCalled = true;
            $loop->stop();
        });

    $loop->run();

    expect($finallyCalled)->toBeTrue();
    expect($error)->toBeInstanceOf(Exception::class);
});

test('Promise::resolve creates resolved promise', function () {
    $loop = Loop::getInstance();
    $value = null;

    $promise = Promise::resolve('resolved');

    $promise->then(function ($val) use (&$value, $loop): void {
        $value = $val;
        $loop->stop();
    });

    $loop->run();

    expect($value)->toBe('resolved');
});

test('Promise::reject creates rejected promise', function () {
    $loop = Loop::getInstance();
    $reason = null;

    $promise = Promise::reject(new RuntimeException('rejected'));

    $promise->catch(function ($e) use (&$reason, $loop): void {
        $reason = $e;
        $loop->stop();
    });

    $loop->run();

    expect($reason)->toBeInstanceOf(RuntimeException::class);
    expect($reason->getMessage())->toBe('rejected');
});

test('Promise::resolve with promise returns same promise', function () {
    $original = Promise::resolve('test');
    $resolved = Promise::resolve($original);

    expect($resolved)->toBe($original);
});

test('Promise executor exception is caught and rejected', function () {
    $loop = Loop::getInstance();
    $reason = null;

    $promise = new Promise(function (): void {
        throw new Exception('executor error');
    });

    $promise->catch(function ($e) use (&$reason, $loop): void {
        $reason = $e;
        $loop->stop();
    });

    $loop->run();

    expect($reason)->toBeInstanceOf(Exception::class);
    expect($reason->getMessage())->toBe('executor error');
});

test('Promise then handler exception is caught', function () {
    $loop = Loop::getInstance();
    $error = null;

    $promise = new Promise(function ($resolve): void {
        $resolve('success');
    });

    $promise
        ->then(function (): void {
            throw new Exception('handler error');
        })
        ->catch(function ($e) use (&$error, $loop): void {
            $error = $e;
            $loop->stop();
        });

    $loop->run();

    expect($error)->toBeInstanceOf(Exception::class);
    expect($error->getMessage())->toBe('handler error');
});

test('Promise chains with promise return value', function () {
    $loop = Loop::getInstance();
    $result = null;

    $promise1 = new Promise(function ($resolve): void {
        $resolve(1);
    });

    $promise2 = new Promise(function ($resolve): void {
        $resolve(2);
    });

    $promise1
        ->then(function () use ($promise2) {
            return $promise2;
        })
        ->then(function ($val) use (&$result, $loop): void {
            $result = $val;
            $loop->stop();
        });

    $loop->run();

    expect($result)->toBe(2);
});
