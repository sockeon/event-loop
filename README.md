# Event Loop

A high-performance, extensible event loop implementation for PHP with support for promises, coroutines, async I/O, and worker pools.

## Features

- 🚀 **High Performance**: Non-blocking I/O with efficient event loop
- 🔄 **Promise Support**: Promise/A+ compliant promises with async/await-like syntax
- 🧵 **Coroutines**: Generator-based coroutines for elegant async code
- 🔌 **Async Sockets**: Non-blocking TCP and Unix socket support
- 👷 **Worker Pools**: Process-based worker pools for true parallelism
- 🔌 **Extensible**: Support for multiple event loop drivers (native, libev, libuv)
- 📦 **Framework Agnostic**: Can be used with any PHP application

## Installation

```bash
composer require sockeon/event-loop
```

## Requirements

- PHP 8.1 or higher
- Optional: libev or libuv extensions for better performance

## Architecture

### Core Components

1. **Event Loop** - Main event loop with driver support
2. **Promises** - Promise/A+ compliant promise implementation
3. **Coroutines** - Generator-based coroutines
4. **Async Sockets** - Non-blocking socket I/O
5. **Worker Pools** - Process-based worker management
6. **Streams** - Readable and writable stream abstractions

## Features to Implement

### Phase 1: Core Event Loop ✅

- [ ] `LoopInterface` - Main event loop interface
- [ ] `Loop` - Singleton event loop instance
- [ ] `DriverInterface` - Driver abstraction
- [ ] `NativeDriver` - Native PHP stream_select driver
- [ ] Basic event loop operations:
  - [ ] `run()` - Start the event loop
  - [ ] `stop()` - Stop the event loop
  - [ ] `defer()` - Schedule callback for next tick
  - [ ] `delay()` - Schedule callback after delay
  - [ ] `repeat()` - Schedule repeating callback
  - [ ] `onReadable()` - Watch for readable events
  - [ ] `onWritable()` - Watch for writable events
  - [ ] `cancel()` - Cancel a watcher

### Phase 2: Promises ✅

- [ ] `PromiseInterface` - Promise/A+ compliant interface
- [ ] `Promise` - Promise implementation
- [ ] `Deferred` - Deferred promise resolver
- [ ] Promise methods:
  - [ ] `then()` - Chain promises
  - [ ] `catch()` - Handle errors
  - [ ] `finally()` - Always execute
  - [ ] `Promise::all()` - Wait for all promises
  - [ ] `Promise::any()` - Wait for any promise
  - [ ] `Promise::race()` - Race promises
  - [ ] `Promise::resolve()` - Create resolved promise
  - [ ] `Promise::reject()` - Create rejected promise

### Phase 3: Coroutines ✅

- [ ] `Coroutine` - Coroutine wrapper
- [ ] Generator-based coroutines
- [ ] Automatic promise unwrapping
- [ ] Exception handling in coroutines
- [ ] Async/await-like syntax support

### Phase 4: Async Sockets ✅

- [ ] `SocketInterface` - Socket abstraction
- [ ] `ServerSocket` - Async server socket
- [ ] `ClientSocket` - Async client socket
- [ ] TCP socket support
- [ ] Unix socket support
- [ ] SSL/TLS support
- [ ] Event-driven I/O:
  - [ ] `on('connection')` - New connection event
  - [ ] `on('data')` - Data received event
  - [ ] `on('close')` - Connection closed event
  - [ ] `on('error')` - Error event
  - [ ] `write()` - Write data
  - [ ] `close()` - Close connection

### Phase 5: Worker Pools ✅

- [ ] `WorkerInterface` - Worker interface
- [ ] `Worker` - Individual worker process
- [ ] `WorkerPool` - Worker pool manager
- [ ] Process forking
- [ ] Task queue
- [ ] Load balancing
- [ ] Worker lifecycle management:
  - [ ] Start workers
  - [ ] Stop workers
  - [ ] Restart crashed workers
  - [ ] Graceful shutdown

### Phase 6: Streams ✅

- [ ] `StreamInterface` - Stream abstraction
- [ ] `ReadableStream` - Readable stream
- [ ] `WritableStream` - Writable stream
- [ ] `DuplexStream` - Bidirectional stream
- [ ] Stream events:
  - [ ] `on('data')` - Data available
  - [ ] `on('end')` - Stream ended
  - [ ] `on('error')` - Stream error
  - [ ] `on('close')` - Stream closed

### Phase 7: Advanced Drivers (Optional) ✅

- [ ] `EvDriver` - libev driver
- [ ] `UvDriver` - libuv driver
- [ ] Driver auto-detection
- [ ] Performance optimizations

## Usage Examples

### Basic Event Loop

```php
use Sockeon\EventLoop\Loop;

$loop = Loop::get();

// Schedule a callback for next tick
$loop->defer(function () {
    echo "This runs on the next tick\n";
});

// Schedule a delayed callback
$loop->delay(2.0, function () {
    echo "This runs after 2 seconds\n";
    $loop->stop(); // Stop the loop
});

// Schedule a repeating callback
$watcher = $loop->repeat(1.0, function () {
    echo "This runs every second\n";
});

// Cancel the watcher after 5 seconds
$loop->delay(5.0, function () use ($watcher) {
    $loop->cancel($watcher);
    echo "Stopped repeating\n";
});

$loop->run();
```

### Promises

```php
use Sockeon\EventLoop\Loop;
use Sockeon\EventLoop\Promise\Promise;
use Sockeon\EventLoop\Promise\Deferred;

$loop = Loop::get();

// Create a promise
$deferred = new Deferred();
$promise = $deferred->promise();

// Chain promises
$promise
    ->then(function ($value) {
        echo "Resolved with: $value\n";
        return $value * 2;
    })
    ->then(function ($value) {
        echo "Doubled: $value\n";
        return $value;
    })
    ->catch(function ($error) {
        echo "Error: " . $error->getMessage() . "\n";
    })
    ->finally(function () {
        echo "Always executed\n";
    });

// Resolve the promise
$loop->defer(function () use ($deferred) {
    $deferred->resolve(42);
});

// Promise::all example
$promises = [
    Promise::resolve(1),
    Promise::resolve(2),
    Promise::resolve(3),
];

Promise::all($promises)->then(function ($values) {
    print_r($values); // [1, 2, 3]
});

$loop->run();
```

### Coroutines

```php
use Sockeon\EventLoop\Loop;
use Sockeon\EventLoop\Coroutine\Coroutine;
use Sockeon\EventLoop\Promise\Promise;

$loop = Loop::get();

// Generator-based coroutine
$coroutine = new Coroutine(function () {
    $result1 = yield Promise::resolve(10);
    $result2 = yield Promise::resolve(20);
    return $result1 + $result2;
});

$coroutine->promise()->then(function ($result) {
    echo "Result: $result\n"; // 30
});

$loop->run();
```

### Async Server Socket

```php
use Sockeon\EventLoop\Loop;
use Sockeon\EventLoop\Socket\ServerSocket;

$loop = Loop::get();

$server = new ServerSocket('0.0.0.0', 6001, $loop);

$server->on('connection', function ($client) use ($loop) {
    echo "New client connected\n";
    
    $client->on('data', function ($data) use ($client) {
        echo "Received: $data\n";
        $client->write("Echo: $data");
    });
    
    $client->on('close', function () {
        echo "Client disconnected\n";
    });
    
    $client->on('error', function ($error) {
        echo "Error: " . $error->getMessage() . "\n";
    });
});

echo "Server listening on 0.0.0.0:6001\n";
$loop->run();
```

### Async Client Socket

```php
use Sockeon\EventLoop\Loop;
use Sockeon\EventLoop\Socket\ClientSocket;

$loop = Loop::get();

$client = new ClientSocket('127.0.0.1', 6001, $loop);

$client->on('connect', function () use ($client) {
    echo "Connected to server\n";
    $client->write("Hello, Server!");
});

$client->on('data', function ($data) {
    echo "Received from server: $data\n";
});

$client->on('close', function () {
    echo "Connection closed\n";
});

$client->connect();
$loop->run();
```

### Worker Pool

```php
use Sockeon\EventLoop\Loop;
use Sockeon\EventLoop\Worker\WorkerPool;
use Sockeon\EventLoop\Worker\Worker;

$loop = Loop::get();

// Create a worker pool with 4 workers
$pool = new WorkerPool(4, $loop);

// Define a task
$task = function ($data) {
    // Heavy computation
    $result = 0;
    for ($i = 0; $i < $data; $i++) {
        $result += $i;
    }
    return $result;
};

// Submit tasks to the pool
for ($i = 0; $i < 10; $i++) {
    $pool->enqueue($task, 1000000)->then(function ($result) use ($i) {
        echo "Task $i completed with result: $result\n";
    });
}

$loop->run();
```

### File I/O with Streams

```php
use Sockeon\EventLoop\Loop;
use Sockeon\EventLoop\Stream\ReadableStream;
use Sockeon\EventLoop\Stream\WritableStream;

$loop = Loop::get();

// Read from a file
$readable = new ReadableStream(fopen('input.txt', 'r'), $loop);

$readable->on('data', function ($chunk) {
    echo "Read: $chunk\n";
});

$readable->on('end', function () {
    echo "File read complete\n";
});

// Write to a file
$writable = new WritableStream(fopen('output.txt', 'w'), $loop);

$writable->write("Hello, World!\n");
$writable->end();

$loop->run();
```

### HTTP Server Example

```php
use Sockeon\EventLoop\Loop;
use Sockeon\EventLoop\Socket\ServerSocket;

$loop = Loop::get();
$server = new ServerSocket('0.0.0.0', 8080, $loop);

$server->on('connection', function ($client) {
    $client->on('data', function ($data) use ($client) {
        // Parse HTTP request
        $lines = explode("\r\n", $data);
        $requestLine = $lines[0];
        
        // Simple HTTP response
        $response = "HTTP/1.1 200 OK\r\n";
        $response .= "Content-Type: text/plain\r\n";
        $response .= "Content-Length: 13\r\n";
        $response .= "\r\n";
        $response .= "Hello, World!";
        
        $client->write($response);
        $client->close();
    });
});

echo "HTTP server listening on http://0.0.0.0:8080\n";
$loop->run();
```

### WebSocket Server Example

```php
use Sockeon\EventLoop\Loop;
use Sockeon\EventLoop\Socket\ServerSocket;

$loop = Loop::get();
$server = new ServerSocket('0.0.0.0', 6001, $loop);

$server->on('connection', function ($client) {
    // Perform WebSocket handshake
    $client->on('data', function ($data) use ($client) {
        // Handle WebSocket frames
        // ... WebSocket frame parsing logic ...
        
        // Send WebSocket frame
        $frame = "\x81" . chr(strlen($data)) . $data;
        $client->write($frame);
    });
});

echo "WebSocket server listening on ws://0.0.0.0:6001\n";
$loop->run();
```

### Combining Promises and Coroutines

```php
use Sockeon\EventLoop\Loop;
use Sockeon\EventLoop\Coroutine\Coroutine;
use Sockeon\EventLoop\Promise\Promise;

$loop = Loop::get();

$asyncOperation = function ($url) {
    return new Promise(function ($resolve, $reject) use ($url) {
        // Simulate async operation
        $loop = Loop::get();
        $loop->delay(1.0, function () use ($resolve, $url) {
            $resolve("Data from $url");
        });
    });
};

$coroutine = new Coroutine(function () use ($asyncOperation) {
    $result1 = yield $asyncOperation('http://api1.example.com');
    $result2 = yield $asyncOperation('http://api2.example.com');
    $result3 = yield $asyncOperation('http://api3.example.com');
    
    return [
        'api1' => $result1,
        'api2' => $result2,
        'api3' => $result3,
    ];
});

$coroutine->promise()->then(function ($results) {
    print_r($results);
});

$loop->run();
```

## API Reference

### Loop

```php
interface LoopInterface
{
    public function run(): void;
    public function stop(): void;
    public function defer(callable $callback): string;
    public function delay(float $delay, callable $callback): string;
    public function repeat(float $interval, callable $callback): string;
    public function onReadable($stream, callable $callback): string;
    public function onWritable($stream, callable $callback): string;
    public function cancel(string $watcherId): void;
    public static function get(): self;
}
```

### Promise

```php
interface PromiseInterface
{
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): PromiseInterface;
    public function catch(callable $onRejected): PromiseInterface;
    public function finally(callable $onFinally): PromiseInterface;
    
    public static function resolve($value): PromiseInterface;
    public static function reject($reason): PromiseInterface;
    public static function all(array $promises): PromiseInterface;
    public static function any(array $promises): PromiseInterface;
    public static function race(array $promises): PromiseInterface;
}
```

### Socket

```php
interface SocketInterface
{
    public function on(string $event, callable $callback): void;
    public function write(string $data): void;
    public function close(): void;
    public function getLocalAddress(): string;
    public function getRemoteAddress(): string;
}
```

## Performance Considerations

- **Native Driver**: Uses PHP's `stream_select()` - good for most use cases
- **libev Driver**: Requires `ext-ev` - better performance for high concurrency
- **libuv Driver**: Requires `ext-uv` - best performance, cross-platform

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Status

🚧 **Work in Progress** - This package is currently under active development.

## Roadmap

- [x] Package structure
- [ ] Phase 1: Core Event Loop
- [ ] Phase 2: Promises
- [ ] Phase 3: Coroutines
- [ ] Phase 4: Async Sockets
- [ ] Phase 5: Worker Pools
- [ ] Phase 6: Streams
- [ ] Phase 7: Advanced Drivers
- [ ] Documentation
- [ ] Tests
- [ ] Examples
