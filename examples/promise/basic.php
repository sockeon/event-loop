<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sockeon\EventLoop\Loop\Loop;
use Sockeon\EventLoop\Promise\Promise;

echo "Basic Promise Example\n";
echo "====================\n\n";

$loop = Loop::getInstance();

// Example 1: Simple promise resolution
echo "1. Simple promise resolution:\n";
$promise = new Promise(function (callable $resolve) {
    $resolve('Hello, World!');
});

$promise->then(function ($value) {
    echo "   - Resolved with: $value\n";
});

$loop->defer(function () use ($loop) {
    $loop->stop();
});
$loop->run();
echo "\n";

// Example 2: Promise chaining
echo "2. Promise chaining:\n";
$promise = new Promise(function (callable $resolve) {
    $resolve(10);
});

$promise
    ->then(function ($value) {
        echo "   - Initial value: $value\n";

        return $value * 2;
    })
    ->then(function ($value) {
        echo "   - Doubled: $value\n";

        return $value + 5;
    })
    ->then(function ($value) use ($loop) {
        echo "   - Final value: $value\n";
        $loop->stop();
    });

$loop->run();
echo "\n";

// Example 3: Error handling
echo "3. Error handling:\n";
$promise = new Promise(function (callable $resolve, callable $reject) {
    $reject(new Exception('Something went wrong!'));
});

$promise
    ->then(function ($value) {
        echo "   - This won't run\n";
    })
    ->catch(function ($error) use ($loop) {
        echo "   - Caught error: " . $error->getMessage() . "\n";
        $loop->stop();
    });

$loop->run();
echo "\n";

// Example 4: Finally handler
echo "4. Finally handler:\n";
$promise = new Promise(function (callable $resolve) {
    $resolve('Success!');
});

$promise
    ->then(function ($value) {
        echo "   - Resolved: $value\n";
    })
    ->finally(function () use ($loop) {
        echo "   - Finally block always runs\n";
        $loop->stop();
    });

$loop->run();
echo "\n";

echo "All examples completed!\n";
