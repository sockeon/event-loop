<?php

declare(strict_types=1);

namespace Sockeon\EventLoop\Coroutine;

use Generator;
use InvalidArgumentException;
use Sockeon\EventLoop\Loop\Loop;
use Sockeon\EventLoop\Promise\Deferred;
use Sockeon\EventLoop\Promise\PromiseInterface;
use Throwable;

/**
 * Coroutine implementation using PHP generators.
 *
 * Allows for async/await-like syntax by automatically unwrapping promises
 * yielded from generators. When a generator yields a promise, the coroutine
 * will wait for that promise to resolve before continuing.
 */
final class Coroutine implements CoroutineInterface
{
    private Generator $generator;

    private Deferred $deferred;

    private PromiseInterface $promise;

    private bool $running = false;

    private bool $completed = false;

    /**
     * Create a new coroutine from a generator.
     *
     * @param Generator $generator The generator to execute
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
        $this->deferred = new Deferred();
        $this->promise = $this->deferred->promise();

        $this->start();
    }

    /**
     * Create a new coroutine from a callable that returns a generator.
     *
     * @param callable $callable The callable that returns a generator
     * @return self The coroutine instance
     */
    public static function create(callable $callable): self
    {
        $generator = $callable();
        if (! $generator instanceof Generator) {
            throw new InvalidArgumentException('Callable must return a Generator');
        }

        return new self($generator);
    }

    /**
     * Start executing the coroutine.
     */
    private function start(): void
    {
        if ($this->running) {
            return;
        }

        $this->running = true;
        if ($this->generator->valid()) {
            $this->tick();
        } else {
            $this->complete($this->generator->getReturn());
        }
    }

    /**
     * Execute one step of the coroutine.
     */
    private function tick(): void
    {
        if ($this->completed) {
            return;
        }

        try {
            while ($this->generator->valid()) {
                $value = $this->generator->current();

                // If the generator yields a promise, wait for it
                if ($value instanceof PromiseInterface) {
                    $value->then(
                        function ($resolvedValue): void {
                            if ($this->completed) {
                                return;
                            }

                            try {
                                $this->generator->send($resolvedValue);
                                Loop::getInstance()->defer(function (): void {
                                    $this->tick();
                                });
                            } catch (Throwable $e) {
                                $this->fail($e);
                            }
                        },
                        function (Throwable $reason): void {
                            if ($this->completed) {
                                return;
                            }

                            try {
                                $this->generator->throw($reason);
                                Loop::getInstance()->defer(function (): void {
                                    $this->tick();
                                });
                            } catch (Throwable $e) {
                                $this->fail($e);
                            }
                        }
                    );

                    return;
                }

                // If it yields a non-promise value, send the yielded value back to the generator
                try {
                    $this->generator->send($value);
                    // Generator state may have changed after send(), loop will check on next iteration
                } catch (Throwable $e) {
                    $this->fail($e);

                    return;
                }
            }

            // Generator has completed (if we exit the loop without returning)
            // @phpstan-ignore-next-line - Generator is not valid after exiting while loop
            if (! $this->completed) {
                $this->complete($this->generator->getReturn());
            }
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }

    /**
     * Complete the coroutine with a value.
     *
     * @param mixed $value The final value
     */
    private function complete($value): void
    {
        if ($this->completed) {
            return;
        }

        $this->completed = true;
        $this->running = false;

        Loop::getInstance()->defer(function () use ($value): void {
            $this->deferred->resolve($value);
        });
    }

    /**
     * Fail the coroutine with an error.
     *
     * @param Throwable $reason The error reason
     */
    private function fail(Throwable $reason): void
    {
        if ($this->completed) {
            return;
        }

        $this->completed = true;
        $this->running = false;

        Loop::getInstance()->defer(function () use ($reason): void {
            $this->deferred->reject($reason);
        });
    }

    /**
     * Get the promise associated with this coroutine.
     *
     * @return PromiseInterface The promise that resolves when the coroutine completes
     */
    public function promise(): PromiseInterface
    {
        return $this->promise;
    }

    /**
     * Check if the coroutine is running.
     *
     * @return bool True if the coroutine is currently running
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Check if the coroutine has completed.
     *
     * @return bool True if the coroutine has completed
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }
}
