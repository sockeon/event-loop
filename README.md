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

- [x] `LoopInterface` - Main event loop interface
- [x] `Loop` - Singleton event loop instance
- [x] `DriverInterface` - Driver abstraction
- [x] `NativeDriver` - Native PHP stream_select driver
- [x] Basic event loop operations:
  - [x] `run()` - Start the event loop
  - [x] `stop()` - Stop the event loop
  - [x] `defer()` - Schedule callback for next tick
  - [x] `delay()` - Schedule callback after delay
  - [x] `repeat()` - Schedule repeating callback
  - [x] `onReadable()` - Watch for readable events
  - [x] `onWritable()` - Watch for writable events
  - [x] `cancel()` - Cancel a watcher

### Phase 2: Promises ✅

- [x] `PromiseInterface` - Promise/A+ compliant interface
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

## Testing

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
