<?php

declare(strict_types=1);

namespace Sockeon\EventLoop\Promise;

use Sockeon\EventLoop\Loop\Loop;
use Throwable;

/**
 * Promise/A+ compliant promise implementation.
 *
 * A promise represents the eventual result of an asynchronous operation.
 * This implementation follows the Promise/A+ specification.
 */
final class Promise implements PromiseInterface
{
    private const STATE_PENDING = 'pending';
    private const STATE_FULFILLED = 'fulfilled';
    private const STATE_REJECTED = 'rejected';

    private string $state = self::STATE_PENDING;

    /** @var mixed */
    private $value = null;

    /** @var Throwable|null */
    private ?Throwable $reason = null;

    /** @var array<int, array{onFulfilled: ?callable, onRejected: ?callable, promise: Promise}> */
    private array $handlers = [];

    /**
     * Create a new promise.
     *
     * @param callable $executor Function that receives resolve and reject callbacks
     */
    public function __construct(callable $executor)
    {
        try {
            $executor(
                function ($value): void {
                    $this->fulfillInternal($value);
                },
                function (Throwable $reason): void {
                    $this->rejectInternal($reason);
                }
            );
        } catch (Throwable $e) {
            $this->rejectInternal($e);
        }
    }

    /**
     * Appends fulfillment and rejection handlers to the promise.
     *
     * @param callable|null $onFulfilled Called when the promise is fulfilled
     * @param callable|null $onRejected Called when the promise is rejected
     * @return PromiseInterface A new promise
     */
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): PromiseInterface
    {
        $promise = new self(function (): void {
            // Empty executor - this promise will be resolved/rejected by handlers
        });

        $this->handlers[] = [
            'onFulfilled' => $onFulfilled,
            'onRejected' => $onRejected,
            'promise' => $promise,
        ];

        if ($this->state !== self::STATE_PENDING) {
            $this->processHandlers();
        }

        return $promise;
    }

    /**
     * Appends a rejection handler to the promise.
     *
     * @param callable $onRejected Called when the promise is rejected
     * @return PromiseInterface A new promise
     */
    public function catch(callable $onRejected): PromiseInterface
    {
        return $this->then(null, $onRejected);
    }

    /**
     * Appends a handler that is always called when the promise is settled.
     *
     * @param callable $onFinally Called when the promise is settled
     * @return PromiseInterface A new promise
     */
    public function finally(callable $onFinally): PromiseInterface
    {
        return $this->then(
            function ($value) use ($onFinally) {
                $onFinally();

                return $value;
            },
            function (Throwable $reason) use ($onFinally) {
                $onFinally();

                throw $reason;
            }
        );
    }

    /**
     * Fulfill the promise with a value internally.
     *
     * @param mixed $value The value to fulfill with
     */
    private function fulfillInternal($value): void
    {
        if ($this->state !== self::STATE_PENDING) {
            return;
        }

        // Handle promise chaining
        if ($value instanceof PromiseInterface) {
            $value->then(
                function ($val): void {
                    $this->fulfillInternal($val);
                },
                function (Throwable $reason): void {
                    $this->rejectInternal($reason);
                }
            );

            return;
        }

        $this->state = self::STATE_FULFILLED;
        $this->value = $value;
        $this->processHandlers();
    }

    /**
     * Reject the promise with a reason internally.
     *
     * @param Throwable $reason The reason for rejection
     */
    private function rejectInternal(Throwable $reason): void
    {
        if ($this->state !== self::STATE_PENDING) {
            return;
        }

        $this->state = self::STATE_REJECTED;
        $this->reason = $reason;
        $this->processHandlers();
    }

    /**
     * Process all queued handlers.
     */
    private function processHandlers(): void
    {
        if (empty($this->handlers)) {
            return;
        }

        $loop = Loop::getInstance();
        $handlers = $this->handlers;
        $this->handlers = [];

        $loop->defer(function () use ($handlers): void {
            foreach ($handlers as $handler) {
                $this->processHandler($handler);
            }
        });
    }

    /**
     * Process a single handler.
     *
     * @param array{onFulfilled: ?callable, onRejected: ?callable, promise: Promise} $handler
     */
    private function processHandler(array $handler): void
    {
        $promise = $handler['promise'];

        try {
            if ($this->state === self::STATE_FULFILLED) {
                if ($handler['onFulfilled'] === null) {
                    $promise->fulfillInternal($this->value);
                } else {
                    $result = ($handler['onFulfilled'])($this->value);
                    // Handle promise chaining - if result is a promise, wait for it
                    if ($result instanceof PromiseInterface) {
                        $result->then(
                            function ($val) use ($promise): void {
                                $promise->fulfillInternal($val);
                            },
                            function (Throwable $reason) use ($promise): void {
                                $promise->rejectInternal($reason);
                            }
                        );
                    } else {
                        $promise->fulfillInternal($result);
                    }
                }
            } elseif ($this->state === self::STATE_REJECTED) {
                if ($handler['onRejected'] === null) {
                    if ($this->reason !== null) {
                        $promise->rejectInternal($this->reason);
                    }
                } else {
                    $result = ($handler['onRejected'])($this->reason);
                    // Handle promise chaining - if result is a promise, wait for it
                    if ($result instanceof PromiseInterface) {
                        $result->then(
                            function ($val) use ($promise): void {
                                $promise->fulfillInternal($val);
                            },
                            function (Throwable $reason) use ($promise): void {
                                $promise->rejectInternal($reason);
                            }
                        );
                    } else {
                        $promise->fulfillInternal($result);
                    }
                }
            }
        } catch (Throwable $e) {
            $promise->rejectInternal($e);
        }
    }

    /**
     * Create a resolved promise.
     *
     * @param mixed $value The value to resolve with
     * @return PromiseInterface A resolved promise
     */
    public static function resolve($value): PromiseInterface
    {
        if ($value instanceof PromiseInterface) {
            return $value;
        }

        return new self(function (callable $resolve) use ($value): void {
            $resolve($value);
        });
    }

    /**
     * Create a rejected promise.
     *
     * @param Throwable $reason The reason for rejection
     * @return PromiseInterface A rejected promise
     */
    public static function reject(Throwable $reason): PromiseInterface
    {
        return new self(function (callable $resolve, callable $reject) use ($reason): void {
            $reject($reason);
        });
    }
}
