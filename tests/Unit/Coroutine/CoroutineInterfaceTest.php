<?php

declare(strict_types=1);

use Sockeon\EventLoop\Coroutine\CoroutineInterface;

test('CoroutineInterface exists', function (): void {
    expect(interface_exists(CoroutineInterface::class))->toBeTrue();
});

test('CoroutineInterface has required methods', function (): void {
    $reflection = new ReflectionClass(CoroutineInterface::class);

    expect($reflection->hasMethod('promise'))->toBeTrue();
    expect($reflection->hasMethod('isRunning'))->toBeTrue();
    expect($reflection->hasMethod('isCompleted'))->toBeTrue();
});

test('CoroutineInterface method signatures are correct', function (): void {
    $reflection = new ReflectionClass(CoroutineInterface::class);

    $promiseMethod = $reflection->getMethod('promise');
    expect($promiseMethod->getReturnType()?->getName())->toBe('Sockeon\EventLoop\Promise\PromiseInterface');

    $isRunningMethod = $reflection->getMethod('isRunning');
    expect($isRunningMethod->getReturnType()?->getName())->toBe('bool');

    $isCompletedMethod = $reflection->getMethod('isCompleted');
    expect($isCompletedMethod->getReturnType()?->getName())->toBe('bool');
});
