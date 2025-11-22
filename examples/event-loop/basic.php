<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sockeon\EventLoop\Loop\Loop;

echo "Basic Event Loop Example\n";
echo "=======================\n\n";

$loop = Loop::getInstance();

// Example 1: Deferred callbacks
echo "1. Deferred callbacks:\n";
$loop->defer(function () {
    echo "   - This runs on the next tick\n";
});

$loop->defer(function () {
    echo "   - This also runs on the next tick\n";
});

$loop->defer(function () use ($loop) {
    echo "   - Stopping the loop\n";
    $loop->stop();
});

$loop->run();
echo "\n";

// Example 2: Delayed callbacks
echo "2. Delayed callbacks:\n";
$start = microtime(true);

$loop->delay(0.1, function () use ($start) {
    $elapsed = microtime(true) - $start;
    echo "   - This runs after ~0.1 seconds (actual: " . round($elapsed, 2) . "s)\n";
});

$loop->delay(0.2, function () use ($loop, $start) {
    $elapsed = microtime(true) - $start;
    echo "   - This runs after ~0.2 seconds (actual: " . round($elapsed, 2) . "s)\n";
    $loop->stop();
});

$loop->run();
echo "\n";

// Example 3: Repeating callbacks
echo "3. Repeating callbacks:\n";
$count = 0;

$loop->repeat(0.1, function () use (&$count, $loop) {
    $count++;
    echo "   - Tick #$count\n";

    if ($count >= 3) {
        echo "   - Stopping after 3 ticks\n";
        $loop->stop();
    }
});

$loop->run();
echo "\n";

echo "All examples completed!\n";
