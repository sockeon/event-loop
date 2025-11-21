<?php

declare(strict_types=1);

namespace Sockeon\EventLoop\Promise;

use RuntimeException;
use Throwable;

/**
 * Deferred promise resolver.
 *
 * Provides a way to create a promise and resolve/reject it from outside
 * the executor function. Useful when you need to resolve a promise based
 * on external events or callbacks.
 */
final class Deferred
{
    private PromiseInterface $promise;

    /** @var callable|null */
    private $resolveCallback = null;

    /** @var callable|null */
    private $rejectCallback = null;

    /**
     * Create a new deferred promise.
     */
    public function __construct()
    {
        $this->promise = new Promise(function (callable $resolve, callable $reject): void {
            $this->resolveCallback = $resolve;
            $this->rejectCallback = $reject;
        });
    }

    /**
     * Get the promise associated with this deferred.
     *
     * @return PromiseInterface The promise
     */
    public function promise(): PromiseInterface
    {
        return $this->promise;
    }

    /**
     * Resolve the promise with a value.
     *
     * @param mixed $value The value to resolve with
     * @throws RuntimeException If the promise has already been resolved or rejected
     */
    public function resolve($value): void
    {
        if ($this->resolveCallback === null) {
            throw new RuntimeException('Promise has already been resolved or rejected');
        }

        $resolve = $this->resolveCallback;
        $this->resolveCallback = null;
        $this->rejectCallback = null;

        $resolve($value);
    }

    /**
     * Reject the promise with a reason.
     *
     * @param Throwable $reason The reason for rejection
     * @throws RuntimeException If the promise has already been resolved or rejected
     */
    public function reject(Throwable $reason): void
    {
        if ($this->rejectCallback === null) {
            throw new RuntimeException('Promise has already been resolved or rejected');
        }

        $reject = $this->rejectCallback;
        $this->resolveCallback = null;
        $this->rejectCallback = null;

        $reject($reason);
    }
}
