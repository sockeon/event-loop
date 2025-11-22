<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sockeon\EventLoop\Loop\Loop;
use Sockeon\EventLoop\Promise\Promise;

echo "Promise Utilities Example\n";
echo "========================\n\n";

$loop = Loop::getInstance();

// Example 1: Promise::all() - Wait for all promises
echo "1. Promise::all() - Wait for all promises:\n";
$promise1 = new Promise(function (callable $resolve) {
    $resolve(1);
});
$promise2 = new Promise(function (callable $resolve) {
    $resolve(2);
});
$promise3 = new Promise(function (callable $resolve) {
    $resolve(3);
});

Promise::all([$promise1, $promise2, $promise3])
    ->then(function ($values) use ($loop) {
        echo "   - All resolved: " . implode(', ', $values) . "\n";
        $loop->stop();
    });

$loop->run();
echo "\n";

// Example 2: Promise::all() with rejection
echo "2. Promise::all() with rejection:\n";
$promise1 = Promise::resolve(1);
$promise2 = Promise::reject(new Exception('Error in promise 2'));
$promise3 = Promise::resolve(3);

Promise::all([$promise1, $promise2, $promise3])
    ->catch(function ($error) use ($loop) {
        echo "   - Rejected: " . $error->getMessage() . "\n";
        $loop->stop();
    });

$loop->run();
echo "\n";

// Example 3: Promise::any() - Wait for any promise
echo "3. Promise::any() - Wait for any promise:\n";
$promise1 = new Promise(function (callable $resolve) {
    $resolve('First');
});
$promise2 = new Promise(function (callable $resolve) {
    $resolve('Second');
});

Promise::any([$promise1, $promise2])
    ->then(function ($value) use ($loop) {
        echo "   - First resolved: $value\n";
        $loop->stop();
    });

$loop->run();
echo "\n";

// Example 4: Promise::race() - Race promises
echo "4. Promise::race() - Race promises:\n";
$promise1 = new Promise(function (callable $resolve) {
    $resolve('Winner 1');
});
$promise2 = new Promise(function (callable $resolve) {
    $resolve('Winner 2');
});

Promise::race([$promise1, $promise2])
    ->then(function ($value) use ($loop) {
        echo "   - Race winner: $value\n";
        $loop->stop();
    });

$loop->run();
echo "\n";

// Example 5: Promise::resolve() and Promise::reject()
echo "5. Promise::resolve() and Promise::reject():\n";
$resolved = Promise::resolve('Immediate value');
$rejected = Promise::reject(new Exception('Immediate error'));

$resolved->then(function ($value) {
    echo "   - Resolved: $value\n";
});

$rejected->catch(function ($error) use ($loop) {
    echo "   - Rejected: " . $error->getMessage() . "\n";
    $loop->stop();
});

$loop->run();
echo "\n";

echo "All examples completed!\n";
