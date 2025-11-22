<?php

declare(strict_types=1);

namespace Sockeon\EventLoop\Coroutine;

use Sockeon\EventLoop\Promise\PromiseInterface;

/**
 * Coroutine interface.
 *
 * Represents a coroutine that can be executed asynchronously.
 * Coroutines allow for async/await-like syntax using PHP generators.
 */
interface CoroutineInterface
{
    /**
     * Get the promise associated with this coroutine.
     *
     * @return PromiseInterface The promise that resolves when the coroutine completes
     */
    public function promise(): PromiseInterface;

    /**
     * Check if the coroutine is running.
     *
     * @return bool True if the coroutine is currently running
     */
    public function isRunning(): bool;

    /**
     * Check if the coroutine has completed.
     *
     * @return bool True if the coroutine has completed
     */
    public function isCompleted(): bool;
}
