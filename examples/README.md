# Examples

This directory contains example code demonstrating how to use the Event Loop and Promise features.

## Running Examples

Make sure you have installed the dependencies:

```bash
composer install
```

Then run any example:

```bash
# Event Loop examples
php examples/event-loop/basic.php

# Promise examples
php examples/promise/basic.php
php examples/promise/utilities.php
php examples/promise/deferred.php
```

## Event Loop Examples

### Basic (`event-loop/basic.php`)

Demonstrates basic event loop operations:
- **Deferred callbacks** - Execute on the next tick
- **Delayed callbacks** - Execute after a specified delay
- **Repeating callbacks** - Execute at regular intervals

## Promise Examples

### Basic (`promise/basic.php`)

Shows fundamental promise usage:
- Simple promise resolution
- Promise chaining with `then()`
- Error handling with `catch()`
- `finally()` handlers

### Utilities (`promise/utilities.php`)

Demonstrates promise utility methods:
- `Promise::all()` - Wait for all promises to resolve
- `Promise::any()` - Wait for any promise to resolve
- `Promise::race()` - Race promises (first to settle wins)
- `Promise::resolve()` and `Promise::reject()` - Static factory methods

### Deferred (`promise/deferred.php`)

Shows how to use `Deferred` for external promise resolution:
- Basic deferred usage
- Async operation simulation
- Error handling with deferred
- Multiple handlers on deferred promises

## Understanding Promise vs Deferred

**Promise:**
- Created with an executor function that runs immediately
- Resolved/rejected from within the executor
- Use when you know how to resolve at creation time

**Deferred:**
- Wraps a Promise and exposes `resolve()`/`reject()` methods
- Allows resolving/rejecting from outside the executor
- Use when resolution depends on external events or callbacks

## More Information

For more details, check the main [README.md](../README.md) and [CONTRIBUTING.md](../CONTRIBUTING.md) files.
