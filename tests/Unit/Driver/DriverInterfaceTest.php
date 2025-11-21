<?php

declare(strict_types=1);

use Sockeon\EventLoop\Driver\DriverInterface;

test('DriverInterface exists and has required methods', function () {
    $reflection = new ReflectionClass(DriverInterface::class);

    expect($reflection->isInterface())->toBeTrue();

    expect($reflection->hasMethod('run'))->toBeTrue();
    expect($reflection->hasMethod('stop'))->toBeTrue();
    expect($reflection->hasMethod('defer'))->toBeTrue();
    expect($reflection->hasMethod('delay'))->toBeTrue();
    expect($reflection->hasMethod('repeat'))->toBeTrue();
    expect($reflection->hasMethod('onReadable'))->toBeTrue();
    expect($reflection->hasMethod('onWritable'))->toBeTrue();
    expect($reflection->hasMethod('cancel'))->toBeTrue();

    $runMethod = $reflection->getMethod('run');
    expect($runMethod->getReturnType()?->getName())->toBe('void');

    $stopMethod = $reflection->getMethod('stop');
    expect($stopMethod->getReturnType()?->getName())->toBe('void');

    $deferMethod = $reflection->getMethod('defer');
    expect($deferMethod->getReturnType()?->getName())->toBe('string');
    expect($deferMethod->getParameters())->toHaveCount(1);

    $delayMethod = $reflection->getMethod('delay');
    expect($delayMethod->getReturnType()?->getName())->toBe('string');
    expect($delayMethod->getParameters())->toHaveCount(2);

    $repeatMethod = $reflection->getMethod('repeat');
    expect($repeatMethod->getReturnType()?->getName())->toBe('string');
    expect($repeatMethod->getParameters())->toHaveCount(2);

    $onReadableMethod = $reflection->getMethod('onReadable');
    expect($onReadableMethod->getReturnType()?->getName())->toBe('string');
    expect($onReadableMethod->getParameters())->toHaveCount(2);

    $onWritableMethod = $reflection->getMethod('onWritable');
    expect($onWritableMethod->getReturnType()?->getName())->toBe('string');
    expect($onWritableMethod->getParameters())->toHaveCount(2);

    $cancelMethod = $reflection->getMethod('cancel');
    expect($cancelMethod->getReturnType()?->getName())->toBe('void');
    expect($cancelMethod->getParameters())->toHaveCount(1);
});

