<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sockeon\EventLoop\Loop\Loop;
use Sockeon\EventLoop\Promise\Deferred;

echo "Deferred Promise Example\n";
echo "=======================\n\n";

$loop = Loop::getInstance();

// Example 1: Basic deferred usage
echo "1. Basic deferred usage:\n";
$deferred = new Deferred();
$promise = $deferred->promise();

$promise->then(function ($value) {
    echo "   - Resolved with: $value\n";
});

// Resolve from outside
$deferred->resolve('Hello from Deferred!');

$loop->defer(function () use ($loop) {
    $loop->stop();
});
$loop->run();
echo "\n";

// Example 2: Deferred with async operation simulation
echo "2. Deferred with async operation:\n";
$deferred = new Deferred();

$deferred->promise()
    ->then(function ($result) {
        echo "   - Operation completed: $result\n";
    });

// Simulate async operation
$loop->delay(0.1, function () use ($deferred) {
    $deferred->resolve('Async operation result');
});

$loop->delay(0.2, function () use ($loop) {
    $loop->stop();
});
$loop->run();
echo "\n";

// Example 3: Deferred with error handling
echo "3. Deferred with error handling:\n";
$deferred = new Deferred();

$deferred->promise()
    ->catch(function ($error) {
        echo "   - Error caught: " . $error->getMessage() . "\n";
    });

// Reject from outside
$deferred->reject(new Exception('Operation failed'));

$loop->defer(function () use ($loop) {
    $loop->stop();
});
$loop->run();
echo "\n";

// Example 4: Multiple handlers on deferred promise
echo "4. Multiple handlers on deferred promise:\n";
$deferred = new Deferred();

$deferred->promise()
    ->then(function ($value) {
        echo "   - Handler 1: $value\n";

        return $value . ' (modified)';
    })
    ->then(function ($value) use ($loop) {
        echo "   - Handler 2: $value\n";
        $loop->stop();
    });

$deferred->resolve('Initial value');

$loop->run();
echo "\n";

echo "All examples completed!\n";
