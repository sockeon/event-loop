<?php

declare(strict_types=1);

use Sockeon\EventLoop\Promise\PromiseInterface;

test('PromiseInterface exists and has required methods', function () {
    $reflection = new ReflectionClass(PromiseInterface::class);

    expect($reflection->isInterface())->toBeTrue();

    expect($reflection->hasMethod('then'))->toBeTrue();
    expect($reflection->hasMethod('catch'))->toBeTrue();
    expect($reflection->hasMethod('finally'))->toBeTrue();

    $thenMethod = $reflection->getMethod('then');
    expect($thenMethod->getReturnType()?->getName())->toBe(PromiseInterface::class);
    $thenParams = $thenMethod->getParameters();
    expect($thenParams)->toHaveCount(2);
    expect($thenParams[0]->getName())->toBe('onFulfilled');
    expect($thenParams[0]->isOptional())->toBeTrue();
    expect($thenParams[0]->getType()?->allowsNull())->toBeTrue();
    expect($thenParams[1]->getName())->toBe('onRejected');
    expect($thenParams[1]->isOptional())->toBeTrue();
    expect($thenParams[1]->getType()?->allowsNull())->toBeTrue();

    $catchMethod = $reflection->getMethod('catch');
    expect($catchMethod->getReturnType()?->getName())->toBe(PromiseInterface::class);
    $catchParams = $catchMethod->getParameters();
    expect($catchParams)->toHaveCount(1);
    expect($catchParams[0]->getName())->toBe('onRejected');
    expect($catchParams[0]->isOptional())->toBeFalse();

    $finallyMethod = $reflection->getMethod('finally');
    expect($finallyMethod->getReturnType()?->getName())->toBe(PromiseInterface::class);
    $finallyParams = $finallyMethod->getParameters();
    expect($finallyParams)->toHaveCount(1);
    expect($finallyParams[0]->getName())->toBe('onFinally');
    expect($finallyParams[0]->isOptional())->toBeFalse();
});
