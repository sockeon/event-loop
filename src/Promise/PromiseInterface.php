<?php

declare(strict_types=1);

namespace Sockeon\EventLoop\Promise;

/**
 * Promise/A+ compliant promise interface.
 *
 * A promise represents the eventual result of an asynchronous operation.
 * Promises can be in one of three states: pending, fulfilled, or rejected.
 */
interface PromiseInterface
{
    /**
     * Appends fulfillment and rejection handlers to the promise.
     *
     * Returns a new promise that resolves to the return value of the handler,
     * or rejects with the reason from the handler.
     *
     * @param callable|null $onFulfilled Called when the promise is fulfilled
     * @param callable|null $onRejected Called when the promise is rejected
     * @return PromiseInterface A new promise
     */
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): PromiseInterface;

    /**
     * Appends a rejection handler to the promise.
     *
     * This is a convenience method equivalent to calling then(null, $onRejected).
     *
     * @param callable $onRejected Called when the promise is rejected
     * @return PromiseInterface A new promise
     */
    public function catch(callable $onRejected): PromiseInterface;

    /**
     * Appends a handler that is always called when the promise is settled.
     *
     * The handler is called regardless of whether the promise is fulfilled or rejected.
     *
     * @param callable $onFinally Called when the promise is settled
     * @return PromiseInterface A new promise
     */
    public function finally(callable $onFinally): PromiseInterface;
}
