<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sockeon\EventLoop\Coroutine\Coroutine;
use Sockeon\EventLoop\Loop\Loop;
use Sockeon\EventLoop\Promise\Promise;

echo "Coroutines: Basic Examples\n";
echo "=========================\n\n";

$loop = Loop::getInstance();

// Example 1: Simple coroutine with promises
echo "Example 1: Simple Promise Unwrapping\n";
echo "-------------------------------------\n";

$coroutine = Coroutine::create(function (): Generator {
    echo "  Starting coroutine...\n";

    // Yield a promise and wait for it to resolve
    $value1 = yield Promise::resolve('Hello');
    echo "  Got value 1: {$value1}\n";

    $value2 = yield Promise::resolve('World');
    echo "  Got value 2: {$value2}\n";

    return $value1 . ' ' . $value2;
});

$coroutine->promise()->then(function ($result) use ($loop): void {
    echo "  Result: {$result}\n";
    $loop->stop();
});

$loop->run();
echo "\n";

// Example 2: Coroutine with delays (simulating async operations)
echo "Example 2: Coroutine with Delays\n";
echo "--------------------------------\n";

$coroutine2 = Coroutine::create(function (): Generator {
    echo "  Step 1: Starting...\n";
    $step1 = yield new Promise(function (callable $resolve): void {
        Loop::getInstance()->delay(0.1, function () use ($resolve): void {
            $resolve('Step 1 completed');
        });
    });
    echo "  {$step1}\n";

    echo "  Step 2: Processing...\n";
    $step2 = yield new Promise(function (callable $resolve): void {
        Loop::getInstance()->delay(0.15, function () use ($resolve): void {
            $resolve('Step 2 completed');
        });
    });
    echo "  {$step2}\n";

    echo "  Step 3: Finalizing...\n";
    $step3 = yield new Promise(function (callable $resolve): void {
        Loop::getInstance()->delay(0.1, function () use ($resolve): void {
            $resolve('Step 3 completed');
        });
    });
    echo "  {$step3}\n";

    return 'All steps completed';
});

$coroutine2->promise()->then(function ($result) use ($loop): void {
    echo "  Final: {$result}\n";
    $loop->stop();
});

$loop->run();
echo "\n";

// Example 3: Error handling
echo "Example 3: Error Handling\n";
echo "--------------------------\n";

$coroutine3 = Coroutine::create(function (): Generator {
    try {
        echo "  Attempting operation...\n";
        $result = yield Promise::reject(new Exception('Something went wrong'));
        echo "  This won't execute\n";
    } catch (Exception $e) {
        echo "  Caught error: {$e->getMessage()}\n";
        echo "  Recovering...\n";
        $recovery = yield Promise::resolve('Recovery successful');
        echo "  {$recovery}\n";

        return $recovery;
    }
});

$coroutine3->promise()->then(function ($result) use ($loop): void {
    echo "  Result: {$result}\n";
    $loop->stop();
});

$loop->run();
echo "\n";

echo "All examples completed!\n";
