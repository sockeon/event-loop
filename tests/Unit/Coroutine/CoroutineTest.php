<?php

declare(strict_types=1);

use Sockeon\EventLoop\Coroutine\Coroutine;
use Sockeon\EventLoop\Coroutine\CoroutineInterface;
use Sockeon\EventLoop\Loop\Loop;
use Sockeon\EventLoop\Promise\Promise;

beforeEach(function (): void {
    Loop::getInstance()->stop();
});

test('Coroutine can be created from generator', function (): void {
    $generator = (function (): Generator {
        yield 1;

        return 2;
    })();

    $coroutine = new Coroutine($generator);

    expect($coroutine)->toBeInstanceOf(CoroutineInterface::class);
    expect($coroutine->promise())->toBeInstanceOf(\Sockeon\EventLoop\Promise\PromiseInterface::class);
});

test('Coroutine can be created using static create method', function (): void {
    $coroutine = Coroutine::create(function (): Generator {
        yield 1;

        return 2;
    });

    expect($coroutine)->toBeInstanceOf(Coroutine::class);
});

test('create method throws exception if callable does not return generator', function (): void {
    expect(function (): void {
        Coroutine::create(function (): int {
            return 1;
        });
    })->toThrow(InvalidArgumentException::class);
});

test('Coroutine automatically unwraps promises', function (): void {
    $result = null;
    $loop = Loop::getInstance();

    $coroutine = Coroutine::create(function () use (&$result): Generator {
        $promise = new Promise(function (callable $resolve): void {
            $resolve('resolved');
        });

        $value = yield $promise;
        $result = $value;

        return $value;
    });

    $coroutine->promise()->then(function ($value) use (&$result, $loop): void {
        $result = $value;
        $loop->stop();
    });

    $loop->run();

    expect($result)->toBe('resolved');
});

test('Coroutine handles promise rejection', function (): void {
    $error = null;
    $loop = Loop::getInstance();

    $coroutine = Coroutine::create(function () use (&$error): Generator {
        $promise = Promise::reject(new Exception('Test error'));

        try {
            yield $promise;
        } catch (Exception $e) {
            $error = $e;

            throw $e;
        }
    });

    $coroutine->promise()->catch(function ($e) use (&$error, $loop): void {
        $error = $e;
        $loop->stop();
    });

    $loop->run();

    expect($error)->toBeInstanceOf(Exception::class);
    if ($error !== null) {
        expect($error->getMessage())->toBe('Test error');
    }
});

test('Coroutine returns final value', function (): void {
    $result = null;
    $loop = Loop::getInstance();

    $coroutine = Coroutine::create(function (): Generator {
        yield Promise::resolve(1);
        yield Promise::resolve(2);

        return 3;
    });

    $coroutine->promise()->then(function ($value) use (&$result, $loop): void {
        $result = $value;
        $loop->stop();
    });

    $loop->run();

    expect($result)->toBe(3);
});

test('Coroutine handles non-promise yields', function (): void {
    $result = null;
    $loop = Loop::getInstance();

    $coroutine = Coroutine::create(function () use (&$result): Generator {
        $value1 = yield 1;
        $value2 = yield 2;
        $result = $value1 + $value2;

        return $result;
    });

    $coroutine->promise()->then(function ($value) use (&$result, $loop): void {
        $result = $value;
        $loop->stop();
    });

    $loop->run();

    expect($result)->toBe(3);
});

test('Coroutine state tracking', function (): void {
    $loop = Loop::getInstance();
    $coroutine = Coroutine::create(function (): Generator {
        yield Promise::resolve(1);

        return 2;
    });

    // Initially running
    expect($coroutine->isRunning())->toBeTrue();
    expect($coroutine->isCompleted())->toBeFalse();

    $coroutine->promise()->then(function () use ($loop): void {
        $loop->stop();
    });

    $loop->run();

    // After completion
    expect($coroutine->isRunning())->toBeFalse();
    expect($coroutine->isCompleted())->toBeTrue();
});

test('Coroutine can chain multiple promises', function (): void {
    $result = null;
    $loop = Loop::getInstance();

    $coroutine = Coroutine::create(function (): Generator {
        $value1 = yield Promise::resolve(10);
        $value2 = yield Promise::resolve(20);
        $value3 = yield Promise::resolve(30);

        return $value1 + $value2 + $value3;
    });

    $coroutine->promise()->then(function ($value) use (&$result, $loop): void {
        $result = $value;
        $loop->stop();
    });

    $loop->run();

    expect($result)->toBe(60);
});

test('Coroutine handles exceptions thrown in generator', function (): void {
    $error = null;
    $loop = Loop::getInstance();

    $coroutine = Coroutine::create(function (): Generator {
        yield Promise::resolve(1);

        throw new Exception('Generator error');
    });

    $coroutine->promise()->catch(function ($e) use (&$error, $loop): void {
        $error = $e;
        $loop->stop();
    });

    $loop->run();

    expect($error)->toBeInstanceOf(Exception::class);
    if ($error !== null) {
        expect($error->getMessage())->toBe('Generator error');
    }
});

test('Coroutine can be used with async/await-like syntax', function (): void {
    $result = null;
    $loop = Loop::getInstance();

    $coroutine = Coroutine::create(function () use (&$result): Generator {
        // Simulate async operations
        $step1 = yield Promise::resolve('Step 1');
        $step2 = yield Promise::resolve('Step 2');
        $step3 = yield Promise::resolve('Step 3');

        $result = $step1 . ' -> ' . $step2 . ' -> ' . $step3;

        return $result;
    });

    $coroutine->promise()->then(function ($value) use (&$result, $loop): void {
        $result = $value;
        $loop->stop();
    });

    $loop->run();

    expect($result)->toBe('Step 1 -> Step 2 -> Step 3');
});
