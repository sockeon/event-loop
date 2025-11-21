<?php

declare(strict_types=1);

namespace Sockeon\EventLoop\Driver;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Native PHP driver using stream_select.
 *
 * This driver uses PHP's built-in stream_select function to provide
 * event loop functionality. It's the default driver and works on all
 * PHP installations without additional extensions.
 */
final class NativeDriver implements DriverInterface
{
    private bool $running = false;
    private bool $stopRequested = false;

    /** @var array<string, callable> */
    private array $deferred = [];

    /** @var array<string, array{callback: callable, time: float}> */
    private array $timers = [];

    /** @var array<string, array{callback: callable, interval: float, time: float}> */
    private array $repeats = [];

    /** @var array<string, array{stream: resource, callback: callable}> */
    private array $readable = [];

    /** @var array<string, array{stream: resource, callback: callable}> */
    private array $writable = [];

    private int $watcherIdCounter = 0;

    /**
     * Start the event loop.
     *
     * This method will block until the event loop is stopped.
     */
    public function run(): void
    {
        if ($this->running) {
            throw new RuntimeException('Event loop is already running');
        }

        $this->running = true;
        $this->stopRequested = false;

        while ($this->running && !$this->stopRequested) {
            $this->tick();
        }

        $this->running = false;
    }

    /**
     * Stop the event loop.
     *
     * This will cause the event loop to exit on the next iteration.
     */
    public function stop(): void
    {
        $this->stopRequested = true;
    }

    /**
     * Schedule a callback to be executed on the next tick of the event loop.
     *
     * @param callable $callback The callback to execute
     * @return string Watcher ID that can be used to cancel the callback
     */
    public function defer(callable $callback): string
    {
        $id = $this->generateWatcherId();
        $this->deferred[$id] = $callback;

        return $id;
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
        if ($delay < 0) {
            throw new InvalidArgumentException('Delay must be non-negative');
        }

        $id = $this->generateWatcherId();
        $this->timers[$id] = [
            'callback' => $callback,
            'time' => microtime(true) + $delay,
        ];

        return $id;
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
        if ($interval < 0) {
            throw new InvalidArgumentException('Interval must be non-negative');
        }

        $id = $this->generateWatcherId();
        $this->repeats[$id] = [
            'callback' => $callback,
            'interval' => $interval,
            'time' => microtime(true) + $interval,
        ];

        return $id;
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
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Stream must be a resource');
        }

        $id = $this->generateWatcherId();
        $this->readable[$id] = [
            'stream' => $stream,
            'callback' => $callback,
        ];

        return $id;
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
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Stream must be a resource');
        }

        $id = $this->generateWatcherId();
        $this->writable[$id] = [
            'stream' => $stream,
            'callback' => $callback,
        ];

        return $id;
    }

    /**
     * Cancel a watcher by its ID.
     *
     * @param string $watcherId The watcher ID returned by defer, delay, repeat, onReadable, or onWritable
     */
    public function cancel(string $watcherId): void
    {
        unset(
            $this->deferred[$watcherId],
            $this->timers[$watcherId],
            $this->repeats[$watcherId],
            $this->readable[$watcherId],
            $this->writable[$watcherId]
        );
    }

    /**
     * Execute one iteration of the event loop.
     */
    private function tick(): void
    {
        // Execute deferred callbacks
        $deferred = $this->deferred;
        $this->deferred = [];
        foreach ($deferred as $callback) {
            try {
                $callback();
            } catch (Throwable $e) {
                // TODO: Add error handling mechanism
                error_log('Uncaught exception in deferred callback: ' . $e->getMessage());
            }
        }

        $now = microtime(true);

        // Execute expired timwatcherIdCounterers
        foreach ($this->timers as $id => $timer) {
            if ($now >= $timer['time']) {
                unset($this->timers[$id]);
                try {
                    $timer['callback']();
                } catch (Throwable $e) {
                    error_log('Uncaught exception in timer callback: ' . $e->getMessage());
                }
            }
        }

        // Execute expired repeats
        foreach ($this->repeats as $id => $repeat) {
            if ($now >= $repeat['time']) {
                $this->repeats[$id]['time'] = $now + $repeat['interval'];
                try {
                    $repeat['callback']();
                } catch (Throwable $e) {
                    error_log('Uncaught exception in repeat callback: ' . $e->getMessage());
                }
            }
        }

        // Calculate timeout for stream_select
        $timeout = $this->calculateTimeout($now);

        // Prepare streams for stream_select
        $read = [];
        $write = [];
        $except = null;

        foreach ($this->readable as $watcher) {
            $read[] = $watcher['stream'];
        }

        foreach ($this->writable as $watcher) {
            $write[] = $watcher['stream'];
        }

        // Use stream_select if we have streams
        // stream_select requires at least one non-empty array in PHP 8.1+
        if (!empty($read) || !empty($write)) {
            // Pass arrays by reference - use empty arrays if needed
            $readRef = $read;
            $writeRef = $write;
            $exceptRef = [];
            
            $result = @stream_select($readRef, $writeRef, $exceptRef, (int) $timeout, (int) (($timeout - (int) $timeout) * 1000000));
            
            // Update arrays from references
            $read = $readRef;
            $write = $writeRef;

            if ($result === false) {
                // Error occurred, continue to next tick
                return;
            }

            // Execute readable callbacks
            foreach ($this->readable as $id => $watcher) {
                if (in_array($watcher['stream'], $read, true)) {
                    try {
                        $watcher['callback']($watcher['stream']);
                    } catch (Throwable $e) {
                        error_log('Uncaught exception in readable callback: ' . $e->getMessage());
                    }
                }
            }

            // Execute writable callbacks
            foreach ($this->writable as $id => $watcher) {
                if (in_array($watcher['stream'], $write, true)) {
                    try {
                        $watcher['callback']($watcher['stream']);
                    } catch (Throwable $e) {
                        error_log('Uncaught exception in writable callback: ' . $e->getMessage());
                    }
                }
            }
        } elseif ($timeout > 0.0) {
            // No streams but we have a timeout, sleep for the timeout duration
            usleep((int) ($timeout * 1000000));
        } else {
            // No streams and no timers, sleep briefly to prevent busy waiting
            usleep(1000); // 1ms
        }
    }

    /**
     * Calculate the timeout for stream_select based on next timer/repeat.
     */
    private function calculateTimeout(float $now): float
    {
        $timeout = null;

        // Find next timer
        foreach ($this->timers as $timer) {
            $remaining = $timer['time'] - $now;
            if ($remaining < 0) {
                $remaining = 0.0;
            }
            if ($timeout === null || $remaining < $timeout) {
                $timeout = $remaining;
            }
        }

        // Find next repeat
        foreach ($this->repeats as $repeat) {
            $remaining = $repeat['time'] - $now;
            if ($remaining < 0) {
                $remaining = 0.0;
            }
            if ($timeout === null || $remaining < $timeout) {
                $timeout = $remaining;
            }
        }

        // If no timers and no streams, return 0 to check stop condition
        if ($timeout === null && empty($this->readable) && empty($this->writable)) {
            return 0.0;
        }

        // Default to 1 second if no timers but we have streams
        if ($timeout === null) {
            $timeout = 1.0;
        }

        // Cap timeout at 1 second to allow periodic checks
        return min($timeout, 1.0);
    }

    /**
     * Generate a unique watcher ID.
     */
    private function generateWatcherId(): string
    {
        return 'watcher_' . (++$this->watcherIdCounter) . '_' . uniqid('', true);
    }
}
