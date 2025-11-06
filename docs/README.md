# Core Module Documentation

This directory contains technical documentation for the `module-core` package.

## Available Documentation

### Message Handling

- **[MessageCollectorInterface](./MessageCollectorInterface.md)** - Comprehensive guide to the MessageCollector system
  - Interface overview and methods
  - Available renderers (Console, JSON, HTML)
  - Usage patterns for services, CLI commands, and controllers
  - Migration guide from MessageStorage
  - Best practices and troubleshooting

## Quick Links

### MessageCollector System

**What is it?**
A format-agnostic message collection system for multi-channel output (CLI, AJAX, HTML).

**When to use it?**
Any time you need to collect operation results and display them in different formats.

**Quick Start:**

```php
// In your service
public function execute(): MessageCollectorInterface
{
    $this->messageCollector->reset();

    $this->messageCollector->addMessage(
        'entity_identifier',
        'Operation completed successfully',
        'success'
    );

    return $this->messageCollector;
}

// In CLI command
$messageCollector = $service->execute();
$this->renderMessages($output, $messageCollector, $output->isVerbose());

// In AJAX controller
$messageCollector = $service->execute();
$renderer = new JsonRenderer();
return $this->resultJson->setData($renderer->render($messageCollector));
```

## Module Overview

The `module-core` package provides foundational functionality used across all Mage2Plenty modules:

- **Framework Components**
  - MessageCollectorInterface - Message collection and rendering
  - SearchMultidimensionalArray - Array searching utilities
  - Various interfaces for common patterns

- **Model Components**
  - EAV attribute helpers
  - Source models
  - Repository interfaces

- **Logger Components**
  - LogProcessorInterface
  - Structured logging support

## Contributing

When adding new features to `module-core`:

1. **Document public interfaces** - All public APIs should be documented
2. **Provide usage examples** - Show real-world usage patterns
3. **Include migration guides** - If replacing existing functionality
4. **Add troubleshooting sections** - Common issues and solutions

## Support

For questions or issues:
- Check the relevant documentation file
- Review code examples in the docs
- Consult the main project documentation at `../../CLAUDE.md`