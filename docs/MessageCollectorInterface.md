# MessageCollectorInterface Documentation

**Last Updated:** October 18, 2025

## Overview

`MessageCollectorInterface` is a format-agnostic message collection system designed for multi-channel output in the Mage2Plenty connector. It provides a standardized way to collect, organize, and render messages across different output formats (CLI, HTML, JSON/AJAX).

**Location:** `SoftCommerce\Core\Framework\MessageCollectorInterface`

## Key Concepts

### Format-Agnostic Design

The MessageCollector follows a separation of concerns pattern:

- **Collection Layer:** `MessageCollectorInterface` - Stores raw message data
- **Rendering Layer:** `RendererInterface` - Transforms data into specific output formats

This design allows the same collected messages to be rendered in multiple formats without duplication.

### Entity-Based Organization

Messages are organized by **entity** (e.g., order increment ID, product SKU, module name). This allows:
- Grouped display of related messages
- Per-entity statistics
- Easy filtering and searching

### Message Structure

Each message contains:
```php
[
    'message' => 'Property created successfully',  // The message text
    'status' => 'success',                         // Status: success, error, warning, info
    'metadata' => [],                              // Additional structured data
    'timestamp' => 1697654400                      // Unix timestamp
]
```

## Interface Methods

### addMessage()

Add a message to the collection.

```php
public function addMessage(
    string $entity,
    string|Phrase $message,
    string $status = 'info',
    array $metadata = []
): void;
```

**Parameters:**
- `$entity` - Entity identifier (e.g., 'order_12345', 'product_sku', 'attribute_set_property')
- `$message` - Message text (string or Magento Phrase object)
- `$status` - One of: `'success'`, `'error'`, `'warning'`, `'info'` (default: `'info'`)
- `$metadata` - Optional array of additional structured data

**Example:**
```php
$messageCollector->addMessage(
    'order_12345',
    'Order exported successfully',
    'success',
    [
        'entity_type' => 'order',
        'plenty_id' => 789,
        'magento_id' => 12345
    ]
);
```

### getMessages()

Retrieve all collected messages organized by entity.

```php
public function getMessages(): array;
```

**Returns:**
```php
[
    'order_12345' => [
        [
            'message' => 'Order exported successfully',
            'status' => 'success',
            'metadata' => ['entity_type' => 'order', 'plenty_id' => 789],
            'timestamp' => 1697654400
        ]
    ],
    'product_ABC123' => [
        [
            'message' => 'Product updated',
            'status' => 'success',
            'metadata' => [],
            'timestamp' => 1697654401
        ]
    ]
]
```

### getEntityMessages()

Get messages for a specific entity.

```php
public function getEntityMessages(string $entity): array;
```

**Example:**
```php
$orderMessages = $messageCollector->getEntityMessages('order_12345');
```

### getStatistics()

Get summary statistics per entity.

```php
public function getStatistics(): array;
```

**Returns:**
```php
[
    'order_12345' => [
        'total' => 5,
        'success' => 3,
        'error' => 1,
        'warning' => 1,
        'info' => 0
    ],
    'product_ABC123' => [
        'total' => 2,
        'success' => 2,
        'error' => 0,
        'warning' => 0,
        'info' => 0
    ]
]
```

### processMessages()

Process batch of messages (typically from MessageStorage for backward compatibility).

```php
public function processMessages(array $messages): void;
```

### reset()

Clear all collected messages and statistics.

```php
public function reset(): void;
```

## Status Values

Messages use lowercase string status values:

| Status | Description | Use Case |
|--------|-------------|----------|
| `'success'` | Operation completed successfully | Record created, updated, exported |
| `'error'` | Operation failed with error | Validation failure, API error, exception |
| `'warning'` | Operation completed with warnings | Partial success, deprecated usage |
| `'info'` | Informational message | Progress updates, skip notices |

**Important:** Always use lowercase strings (`'success'`, not `StatusInterface::SUCCESS` constants) as these are the values stored and compared by renderers.

## Available Renderers

### 1. ConsoleRenderer

**Purpose:** CLI/Terminal output
**Location:** `SoftCommerce\Core\Framework\MessageCollector\ConsoleRenderer`
**Supports:** `'cli'`, `'console'`, `'terminal'`

**Features:**
- Colored output with icons (✓/✗/⚠)
- Summary and detailed views
- Timestamp display in verbose mode
- Automatic verbosity detection

**Usage:**
```php
$renderer = new ConsoleRenderer($output);
$renderer->render($messageCollector, [
    'verbose' => $output->isVerbose()
]);
```

**Output Example:**
```
Export Summary:
  ✓ order_12345: Order 789
  ✗ product_ABC: Failed to export
  ⚠ category_10: Partial update

Total: 2 entities processed (2 successful operations, 1 errors)
```

**Helper Method (in AbstractCommand):**
```php
$this->renderMessages($output, $messageCollector, $output->isVerbose());
```

### 2. JsonRenderer

**Purpose:** AJAX/API responses
**Location:** `SoftCommerce\Core\Framework\MessageCollector\JsonRenderer`
**Supports:** `'json'`, `'ajax'`, `'api'`

**Features:**
- Structured JSON output
- Messages categorized by status
- Summary statistics
- Items summary for successful operations

**Usage:**
```php
$renderer = new JsonRenderer();
$result = $renderer->render($messageCollector, [
    'show_details' => true
]);

return $this->resultJson->setData($result);
```

**Output Structure:**
```json
{
    "success": true,
    "error": false,
    "summary": {
        "totals": {
            "success": 5,
            "error": 1,
            "warning": 0
        },
        "by_entity": {
            "order_12345": {
                "total": 3,
                "success": 2,
                "error": 1
            }
        }
    },
    "statistics": { ... },
    "messages": [
        "[Order 12345] Order exported successfully"
    ],
    "errors": [
        "[Product ABC] Validation failed"
    ],
    "items": {
        "Orders": 2,
        "Products": 3
    }
}
```

### 3. HtmlRenderer

**Purpose:** Admin interface display
**Location:** `SoftCommerce\Core\Framework\MessageCollector\HtmlRenderer`
**Supports:** `'html'`, `'web'`, `'admin'`

**Features:**
- Magento admin grid styling
- Color-coded status indicators
- Inline CSS styles
- Summary and detailed views

**Usage:**
```php
$renderer = new HtmlRenderer();
$html = $renderer->render($messageCollector, [
    'show_details' => true,
    'skip_styles' => false
]);
```

**Renders:**
- Summary table with Magento admin styling
- Detailed message lists with color coding
- Timestamps for each message
- Grid severity indicators

## RendererInterface

All renderers implement `RendererInterface` with these methods:

### render()

Full rendering with summary and optional details.

```php
public function render(MessageCollectorInterface $collector, array $options = []);
```

### renderSummary()

Summary only (totals, statistics).

```php
public function renderSummary(MessageCollectorInterface $collector, array $options = []);
```

### renderDetails()

Detailed message list.

```php
public function renderDetails(MessageCollectorInterface $collector, array $options = []);
```

### supports()

Check if renderer supports specific output type.

```php
public function supports(string $outputType): bool;
```

## Common Options

Options are renderer-specific, but common patterns:

| Option | Type | Renderers | Description |
|--------|------|-----------|-------------|
| `verbose` | bool | Console | Show detailed messages with timestamps |
| `show_details` | bool | JSON, HTML | Include detailed message list |
| `skip_styles` | bool | HTML | Don't include inline CSS |

## Usage Patterns

### Pattern 1: Service Layer

Services return `MessageCollectorInterface`:

```php
class PropertyExportService
{
    public function execute(): MessageCollectorInterface
    {
        $this->messageCollector->reset();

        foreach ($properties as $property) {
            try {
                $this->exportProperty($property);
                $this->messageCollector->addMessage(
                    $property->getAttributeCode(),
                    __('Property exported successfully'),
                    'success',
                    [
                        'entity_type' => 'property',
                        'plenty_id' => $result['id']
                    ]
                );
            } catch (\Exception $e) {
                $this->messageCollector->addMessage(
                    $property->getAttributeCode(),
                    __('Export failed: %1', $e->getMessage()),
                    'error'
                );
            }
        }

        return $this->messageCollector;
    }
}
```

### Pattern 2: CLI Commands

Commands render messages to console:

```php
class ExportPropertyCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $service = $this->serviceFactory->create();
        $messageCollector = $service->execute();

        // Use helper method from AbstractCommand
        $this->renderMessages($output, $messageCollector, $output->isVerbose());

        return Cli::RETURN_SUCCESS;
    }
}
```

### Pattern 3: Controller Actions (AJAX)

Controllers return JSON responses:

```php
class ExportAction extends Action
{
    public function execute()
    {
        $service = $this->serviceFactory->create();
        $messageCollector = $service->execute();

        $renderer = new JsonRenderer();
        $result = $renderer->render($messageCollector);

        return $this->resultJson->setData($result);
    }
}
```

### Pattern 4: Controller Actions (HTML)

Controllers for admin pages:

```php
class CollectConfiguration extends Action
{
    public function execute()
    {
        $messageCollector = $this->service->execute();

        // Process for DataObject (legacy compatibility)
        $dataObject = new DataObject();

        foreach ($messageCollector->getMessages() as $entity => $entityMessages) {
            foreach ($entityMessages as $message) {
                $status = $message['status'] ?? 'info';

                if ($status === 'success') {
                    $messages[] = __('[%1] %2', $entity, $message['message']);
                } elseif ($status === 'error') {
                    $errors[] = __('[%1] %2', $entity, $message['message']);
                }
            }
        }

        $dataObject->setData('messages', $messages);
        $dataObject->setData('errors', $errors);

        return $this->createJsonResponse($dataObject);
    }
}
```

## Migration from MessageStorage

When migrating from `MessageStorageInterface` to `MessageCollectorInterface`:

### Method Signature Change

```php
// OLD - MessageStorage
$messageStorage->addData(
    $message,  // Message first
    $entity,   // Entity second
    $status    // Status third
);

// NEW - MessageCollector
$messageCollector->addMessage(
    $entity,   // Entity first
    $message,  // Message second
    $status    // Status third (optional, defaults to 'info')
);
```

### Status Value Change

```php
// OLD - MessageStorage uses constants
StatusInterface::SUCCESS  // Integer or constant value

// NEW - MessageCollector uses strings
'success'  // Lowercase string
```

### Return Type Change

```php
// OLD
public function execute(): MessageStorageInterface

// NEW
public function execute(): MessageCollectorInterface
```

### Retrieval Change

```php
// OLD
$messages = $messageStorage->getData();

// NEW
$messages = $messageCollector->getMessages();
```

## Best Practices

### 1. Use Entity Identifiers Consistently

```php
// Good - Unique, meaningful identifiers
$messageCollector->addMessage('order_12345', ...);
$messageCollector->addMessage('product_SKU123', ...);
$messageCollector->addMessage('attribute_color', ...);

// Avoid - Generic identifiers
$messageCollector->addMessage('item', ...);
$messageCollector->addMessage('entity_1', ...);
```

### 2. Include Metadata for Context

```php
$messageCollector->addMessage(
    'order_12345',
    'Order exported successfully',
    'success',
    [
        'entity_type' => 'order',
        'magento_id' => 12345,
        'plenty_id' => 789,
        'operation' => 'export'
    ]
);
```

### 3. Use Appropriate Status Levels

```php
// Success - Operation completed
'success' => 'Order created in PlentyONE'

// Error - Operation failed
'error' => 'API request failed: Connection timeout'

// Warning - Partial success or deprecated
'warning' => 'Product updated but images skipped'

// Info - Progress or skip information
'info' => 'Property already exists. Skipping...'
```

### 4. Reset Before New Operations

```php
public function execute(): MessageCollectorInterface
{
    $this->messageCollector->reset();  // Clear previous messages

    // ... perform operations ...

    return $this->messageCollector;
}
```

### 5. Delegate execute() to executeWithCallback()

When you have both methods, use delegation to avoid duplication:

```php
public function execute(array $modules = []): MessageCollectorInterface
{
    return $this->executeWithCallback($modules);
}

public function executeWithCallback(array $modules = [], ?callable $callback = null): MessageCollectorInterface
{
    $this->messageCollector->reset();

    // Main logic here
    $this->messageCollector->addMessage(...);

    if ($callback) {
        $callback($entity, $message, $status);
    }

    return $this->messageCollector;
}
```

## Troubleshooting

### Messages Not Displaying

**Problem:** Messages collected but not showing in output.

**Solutions:**
1. Check if `getMessages()` returns data: `var_dump($messageCollector->getMessages())`
2. Verify status values are lowercase strings: `'success'` not `StatusInterface::SUCCESS`
3. Ensure renderer is properly instantiated and called
4. Check verbosity setting for CLI commands

### Wrong Message Count

**Problem:** Statistics show incorrect counts.

**Solutions:**
1. Verify status values match expected strings (`'success'`, `'error'`, etc.)
2. Check for duplicate `addMessage()` calls
3. Ensure `reset()` is called at the start of operations
4. Look for messages with unrecognized status values (defaults to `'info'`)

### Status Comparison Failing

**Problem:** `if ($status === StatusInterface::SUCCESS)` always false.

**Solution:** Use lowercase strings:
```php
// Wrong
if ($status === StatusInterface::SUCCESS)

// Correct
if ($status === 'success')
```

## See Also

- [MessageStorage to MessageCollector Migration Guide](./MessageCollector-Migration.md) _(to be created)_
- [Creating Custom Renderers](./Custom-Renderers.md) _(to be created)_
- Renderer implementations in `packages/modules/module-core/Framework/MessageCollector/`