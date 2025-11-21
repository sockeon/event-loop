<?php

declare(strict_types=1);

namespace Sockeon\EventLoop\Loop;

use RuntimeException;
use Sockeon\EventLoop\Driver\DriverInterface;
use Sockeon\EventLoop\Driver\NativeDriver;

/**
 * Singleton event loop instance.
 *
 * Provides a global access point to the event loop.
 * This class implements the singleton pattern to ensure
 * only one event loop instance exists per application.
 */
final class Loop implements LoopInterface
{
    private static ?self $instance = null;

    private ?DriverInterface $driver = null;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Get the event loop driver.
     *
     * @return DriverInterface The driver instance
     */
    private function getDriver(): DriverInterface
    {
        if ($this->driver === null) {
            $this->driver = new NativeDriver();
        }

        return $this->driver;
    }

    /**
     * Get the singleton instance of the event loop.
     *
     * @return self The event loop instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Prevent cloning of the singleton instance.
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization of the singleton instance.
     */
    public function __wakeup(): void
    {
        throw new RuntimeException('Cannot unserialize singleton');
    }

    /**
     * Start the event loop.
     *
     * This method will block until the event loop is stopped.
     */
    public function run(): void
    {
        $this->getDriver()->run();
    }

    /**
     * Stop the event loop.
     *
     * This will cause the event loop to exit on the next iteration.
     */
    public function stop(): void
    {
        $this->getDriver()->stop();
    }

    /**
     * Schedule a callback to be executed on the next tick of the event loop.
     *
     * @param callable $callback The callback to execute
     * @return string Watcher ID that can be used to cancel the callback
     */
    public function defer(callable $callback): string
    {
        return $this->getDriver()->defer($callback);
    }

    /**
     * Schedule a callback to be executed after a specified delay.
     *
     * @param float $delay Delay in seconds
     * @param callable $callback The callback to execute
     * @return string Watcher ID that can be used to cancel the callback
     */
    public function delay(float $delay, callable $callback): string
    {
        return $this->getDriver()->delay($delay, $callback);
    }

    /**
     * Schedule a callback to be executed repeatedly at a specified interval.
     *
     * @param float $interval Interval in seconds between executions
     * @param callable $callback The callback to execute
     * @return string Watcher ID that can be used to cancel the callback
     */
    public function repeat(float $interval, callable $callback): string
    {
        return $this->getDriver()->repeat($interval, $callback);
    }

    /**
     * Watch a stream for readable events.
     *
     * @param resource $stream The stream resource to watch
     * @param callable $callback The callback to execute when the stream is readable
     * @return string Watcher ID that can be used to cancel the watcher
     */
    public function onReadable($stream, callable $callback): string
    {
        return $this->getDriver()->onReadable($stream, $callback);
    }

    /**
     * Watch a stream for writable events.
     *
     * @param resource $stream The stream resource to watch
     * @param callable $callback The callback to execute when the stream is writable
     * @return string Watcher ID that can be used to cancel the watcher
     */
    public function onWritable($stream, callable $callback): string
    {
        return $this->getDriver()->onWritable($stream, $callback);
    }

    /**
     * Cancel a watcher by its ID.
     *
     * @param string $watcherId The watcher ID returned by defer, delay, repeat, onReadable, or onWritable
     */
    public function cancel(string $watcherId): void
    {
        $this->getDriver()->cancel($watcherId);
    }
}
