# Contributing to Event Loop

Thank you for your interest in contributing to the Event Loop project! This document provides guidelines and instructions for contributing.

## Code of Conduct

- Be respectful and inclusive
- Welcome newcomers and help them learn
- Focus on constructive feedback
- Respect different viewpoints and experiences

## How to Contribute

### Reporting Bugs

If you find a bug, please open an issue with:

- A clear, descriptive title
- Steps to reproduce the issue
- Expected behavior
- Actual behavior
- PHP version and environment details
- Any relevant code snippets or error messages

### Suggesting Features

Feature suggestions are welcome! Please open an issue with:

- A clear description of the feature
- Use cases and examples
- Potential implementation approach (if you have ideas)
- Any related issues or discussions

### Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Ensure tests pass and code quality checks succeed
5. Commit your changes with clear, descriptive messages
6. Push to your fork (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## Development Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- Git

### Installation

1. Clone the repository:
```bash
git clone https://github.com/sockeon/php-event-loop.git
cd php-event-loop
```

2. Install dependencies:
```bash
composer install
```

3. Run tests to verify everything works:
```bash
composer test
```

## Coding Standards

### PHP Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style
- Use type hints and return types wherever possible
- Write self-documenting code with clear variable and method names
- Add PHPDoc comments for public methods and classes

### Code Quality

- All code must pass PHP CS Fixer checks (PSR-12 standard)
- All code must pass PHPStan level 10 analysis
- All tests must pass
- Code should be maintainable and well-structured

### Running Code Quality Checks

```bash
# Run PHP CS Fixer to check code style
composer cs-check

# Run PHP CS Fixer to fix code style issues
composer cs-fix

# Run PHPStan
composer phpstan

# Run tests
composer test
```

## Testing

### Writing Tests

- Use Pest PHP for testing
- Write tests for all new features
- Ensure existing tests continue to pass
- Aim for high code coverage
- Test both success and error cases

### Test Structure

- Unit tests go in `tests/Unit/`
- Feature tests go in `tests/Feature/`
- Test files should be named `*Test.php`

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
vendor/bin/pest --coverage
```

## Commit Messages

Write clear, descriptive commit messages:

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests when applicable

Example:
```
Add support for libev driver

Implements EvDriver class that uses ext-ev for better performance
in high-concurrency scenarios. Includes automatic driver detection
and fallback to native driver.

Closes #42
```

## Pull Request Process

1. **Update Documentation**: Update README.md or other documentation if needed
2. **Add Tests**: Ensure new code is covered by tests
3. **Update CHANGELOG**: Add an entry describing your changes (if applicable)
4. **Check CI**: Ensure all CI checks pass
5. **Request Review**: Request review from maintainers

### PR Checklist

- [ ] Code follows the project's coding standards
- [ ] Tests pass locally
- [ ] PHPStan analysis passes
- [ ] Documentation is updated (if needed)
- [ ] Commit messages are clear and descriptive
- [ ] Branch is up to date with main

## Project Structure

```
php-event-loop/
├── src/              # Source code
├── tests/            # Test files
│   ├── Unit/        # Unit tests
│   └── Feature/     # Feature tests
├── composer.json     # Dependencies
├── phpunit.xml       # PHPUnit configuration
└── phpstan.neon      # PHPStan configuration
```

## Getting Help

- Open an issue for questions or discussions
- Check existing issues and pull requests
- Review the README for project overview

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

## Thank You!

Your contributions make this project better for everyone. Thank you for taking the time to contribute!

