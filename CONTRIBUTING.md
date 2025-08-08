# Contributing to Harryes Facebook Graph API Package

Thank you for your interest in contributing to the Harryes Facebook Graph API Package! This document provides guidelines and information for contributors.

## 🤝 How to Contribute

### Reporting Issues

Before creating an issue, please:

1. **Search existing issues** to see if your problem has already been reported
2. **Check the documentation** to ensure you're using the package correctly
3. **Provide detailed information** including:
   - Laravel version
   - PHP version
   - Package version
   - Error messages
   - Steps to reproduce
   - Expected vs actual behavior

### Feature Requests

When requesting features:

1. **Describe the use case** clearly
2. **Explain the benefits** of the feature
3. **Provide examples** of how it would be used
4. **Consider implementation** complexity

### Pull Requests

We welcome pull requests! Here's how to contribute:

#### 1. Fork the Repository

Fork the repository to your GitHub account.

#### 2. Create a Feature Branch

```bash
git checkout -b feature/your-feature-name
```

#### 3. Make Your Changes

- Follow the coding standards (see below)
- Add tests for new functionality
- Update documentation if needed
- Keep commits atomic and well-described

#### 4. Run Tests

```bash
composer test
```

Ensure all tests pass before submitting your PR.

#### 5. Submit Your Pull Request

- Provide a clear description of your changes
- Reference any related issues
- Include test coverage information

## 📋 Coding Standards

### PHP Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use PHP 8.1+ features where appropriate
- Add proper type hints and return types
- Include PHPDoc comments for public methods

### Laravel Standards

- Follow Laravel conventions
- Use Laravel's built-in features when possible
- Follow Laravel's naming conventions
- Use dependency injection

### Code Style

```php
<?php

namespace LaravelFacebookGraphApi\YourNamespace;

use LaravelFacebookGraphApi\Contracts\SomeInterface;

class YourClass implements SomeInterface
{
    public function __construct(
        private SomeService $service
    ) {}

    public function someMethod(string $parameter): array
    {
        // Your implementation
        return [];
    }
}
```

## 🧪 Testing Guidelines

### Writing Tests

- Write tests for all new functionality
- Use descriptive test method names
- Follow the `it_should_` or `test_` naming convention
- Mock external dependencies
- Test both success and failure scenarios

### Test Structure

```php
<?php

namespace LaravelFacebookGraphApi\Tests\Unit;

use LaravelFacebookGraphApi\Tests\TestCase;

class YourClassTest extends TestCase
{
    /** @test */
    public function it_should_do_something_when_condition_is_met()
    {
        // Arrange
        $input = 'test';
        
        // Act
        $result = $this->service->process($input);
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

## 📚 Documentation

### Code Documentation

- Add PHPDoc comments for all public methods
- Include parameter and return type descriptions
- Provide usage examples in comments
- Document exceptions that may be thrown

### README Updates

When adding new features:

1. Update the README.md with usage examples
2. Add the feature to the features list
3. Update the API reference section
4. Include migration notes if breaking changes

## 🔄 Release Process

### Versioning

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Release Checklist

Before releasing:

- [ ] All tests pass
- [ ] Documentation is updated
- [ ] CHANGELOG.md is updated
- [ ] Version is bumped in composer.json
- [ ] Tag is created on GitHub

## 🐛 Bug Reports

When reporting bugs, please include:

### Required Information

- **Package Version**: The version you're using
- **Laravel Version**: Your Laravel version
- **PHP Version**: Your PHP version
- **Environment**: Development/production
- **Error Message**: Full error message and stack trace

### Optional but Helpful

- **Code Example**: Minimal code to reproduce the issue
- **Expected Behavior**: What you expected to happen
- **Actual Behavior**: What actually happened
- **Screenshots**: If applicable

## 💡 Development Setup

### Prerequisites

- PHP 8.1+
- Composer
- Git

### Local Development

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/facebook-graph-api.git
   cd facebook-graph-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Run tests**
   ```bash
   composer test
   ```

4. **Check code style**
   ```bash
   composer lint
   ```

### Testing with a Laravel Application

1. **Link the package locally**
   ```bash
   composer config repositories.facebook-graph-api path /path/to/your/package
   composer require laravel-facebook-graph-api/facebook-graph-api:dev-master
   ```

2. **Publish configuration**
   ```bash
   php artisan vendor:publish --tag="facebook-graph-api-config"
   ```

## 📞 Getting Help

If you need help contributing:

- **GitHub Issues**: For bug reports and feature requests
- **GitHub Discussions**: For general questions and discussions
- **Documentation**: Check the README.md for usage examples

## 🙏 Recognition

Contributors will be recognized in:

- The README.md file
- Release notes
- GitHub contributors list

## 📄 License

By contributing to this project, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to the Harryes Facebook Graph API Package! 🚀 