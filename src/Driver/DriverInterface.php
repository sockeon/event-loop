<?php

declare(strict_types=1);

namespace Sockeon\EventLoop\Driver;

/**
 * Driver interface for event loop implementations.
 *
 * Drivers provide the actual implementation of event loop operations.
 * Different drivers can be used (native PHP, libev, libuv, etc.)
 * to provide different performance characteristics.
 */
interface DriverInterface
{
    /**
     * Start the event loop.
     *
     * This method will block until the event loop is stopped.
     */
    public function run(): void;

    /**
     * Stop the event loop.
     *
     * This will cause the event loop to exit on the next iteration.
     */
    public function stop(): void;

    /**
     * Schedule a callback to be executed on the next tick of the event loop.
     *
     * @param callable $callback The callback to execute
     * @return string Watcher ID that can be used to cancel the callback
     */
    public function defer(callable $callback): string;

    /**
     * Schedule a callback to be executed after a specified delay.
     *
     * @param float $delay Delay in seconds
     * @param callable $callback The callback to execute
     * @return string Watcher ID that can be used to cancel the callback
     */
    public function delay(float $delay, callable $callback): string;

    /**
     * Schedule a callback to be executed repeatedly at a specified interval.
     *
     * @param float $interval Interval in seconds between executions
     * @param callable $callback The callback to execute
     * @return string Watcher ID that can be used to cancel the callback
     */
    public function repeat(float $interval, callable $callback): string;

    /**
     * Watch a stream for readable events.
     *
     * @param resource $stream The stream resource to watch
     * @param callable $callback The callback to execute when the stream is readable
     * @return string Watcher ID that can be used to cancel the watcher
     */
    public function onReadable($stream, callable $callback): string;

    /**
     * Watch a stream for writable events.
     *
     * @param resource $stream The stream resource to watch
     * @param callable $callback The callback to execute when the stream is writable
     * @return string Watcher ID that can be used to cancel the watcher
     */
    public function onWritable($stream, callable $callback): string;

    /**
     * Cancel a watcher by its ID.
     *
     * @param string $watcherId The watcher ID returned by defer, delay, repeat, onReadable, or onWritable
     */
    public function cancel(string $watcherId): void;
}

