# Coding Standards

These standards are derived from the nostripub codebase patterns and ensure consistency across the project.

## Line Endings

Always use **Linux line endings (LF)**. Never use Windows line endings (CRLF).

Configure your editor:
```bash
git config --global core.autocrlf input
```

## PHP Version

- **Minimum:** PHP 8.4
- Use latest PHP features: readonly classes, constructor property promotion, first-class callables

## Files

### Structure

```php
<?php

namespace nostriphant\nostripub;

final readonly class ClassName {
    
    public function __construct(private string $dependency) {
        
    }
}
```

### Naming

- Files: `PascalCase.php`
- Classes: `PascalCase`
- Methods/variables: `snake_case`
- Constants: `SCREAMING_SNAKE_CASE`
- Private class properties: `snake_case` (not prefixed with `_`)

### Braces

- Opening brace on same line for classes/functions
- Opening brace on new line for control structures (allman-style for blocks only)

## Classes

### Readonly Classes

Use `final readonly` for immutable data classes:

```php
final readonly class WebfingerResource {
    public function __construct(
        private string $browser_hostname, 
        private \Closure $nip05_lookup
    ) {
        
    }
}
```

### Constructor Property Promotion

Always use PHP 8 constructor property promotion:

```php
// Good
public function __construct(private string $cache) {}

// Avoid
private string $cache;
public function __construct(string $cache) {
    $this->cache = $cache;
}
```

### Visibility

- Classes: `final readonly` by default
- Methods: Only `__construct` and `__invoke` are public. All other methods are private.
- No `protected` unless extension is anticipated

## Functions and Closures

### Return Types

Always declare return types:

```php
public function __invoke(string $requested_resource): NIP05 {
    // ...
}

public static function lookup(array $discovery_relays, callable $http, callable $error): \Closure {
    return function(string $nip05_identifier) use ($discovery_relays, $http, $error): self {
        // ...
    };
}
```

### Callable Parameters

Use `\Closure` type hint for explicit closures:

```php
public static function lookup(..., callable $error): \Closure {
```

## Variables and Arrays

### Naming

```php
$browser_hostname  // Good
$browserScheme     // Avoid - use snake_case for variables
$discovery_relays  // Good
$relay_list        // Avoid
```

### Array Declaration

Use short array syntax:

```php
$array = ['key' => 'value'];
$array = [];
```

### Array Access

Always use null coalescing for potentially undefined keys:

```php
// Good
$value = $array['key'] ?? null;
$json['names'][$nostr_user] ?? $discovery_relays;

// Acceptable when key existence is guaranteed
$array['required_key']
```

## Control Flow

### Conditionals

Use strict comparison:

```php
if ($value === true) {}
if ($exists === false) {}
if (isset($array[$key]) === false) {}
```

Use `str_contains()`, `str_starts_with()`, `str_ends_with()` for string checks.

### Early Returns

Prefer early returns to reduce nesting:

```php
if (isset($_GET['resource']) === false) {
    header('HTTP/1.1 400 Bad Request', true);
    exit('Bad Request');
}

// Main logic at base level
```

## HTTP and Curl

### Response Codes

Use string codes that map to HTTP status:

```php
$message = match($code) {
    '422' => 'Unprocessable Content',
    '404' => 'Not Found',
    '400' => 'Bad Request',
};
header('HTTP/1.1 ' . $code . ' ' . $message, true);
```

### Curl Configuration

Always set timeouts:

```php
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
]);
```

### Content Type

WebFinger uses RFC 7033 standard:

```php
header('Content-Type: application/jrd+json', true);
```

## Error Handling

### Exit for HTTP Responses

Acceptable use of `exit` for terminating HTTP responses in route handlers:

```php
if (isset($_GET['resource']) === false) {
    header('HTTP/1.1 400 Bad Request', true);
    exit('Bad Request');
}
```

### Error Logging

Use `error_log()` for diagnostics:

```php
error_log('Connecting to ' . $discovery_relay);
```

## Routes

### File Structure

Routes are functional, returning closures that handle requests:

```php
<?php
// routes/.well-known/webfinger.php

use nostriphant\nostripub\NIP05;

// Route logic, no class wrapper
$http = new \nostriphant\nostripub\HTTP(CACHE_DIR);
// ...
```

### Path Security

Validate all user-provided paths before using in filesystem operations:

```php
if (!preg_match('#^/\.well-known/webfinger$#', $normalized)) {
    http_response_code(404);
    exit('Not Found');
}
```

## Testing

### Framework

Use Pest PHP for testing.

### Test Organization

```
tests/
├── Pest.php              # Pest configuration
├── AcceptanceCase.php    # Base class for acceptance tests
├── Acceptance/
│   └── WebFingerTest.php # Feature tests
└── Unit/
```

### Acceptance Tests

Extend `AcceptanceCase` and use Pest's `describe`/`it`:

```php
describe('webfinger', function() {
    it('responds with a 400 status code for a missing resource')
        ->get('/.well-known/webfinger')
        ->status->toBe('400');
});
```

### Test Process Management

Use the `Process` class for integration tests:

```php
beforeAll(function() use (&$process) {
    $process = new \nostriphant\nostripubTests\Process('api', $cmd, $env, $runtest);
    sleep(1);
});

afterAll(function() use (&$process) {
    $process();
});
```

## Dependencies

### Vendor Dependencies

Use established libraries:
- `nostriphant/client` - Nostr relay communication
- `nostriphant/nip-19` - NIP-19 encoding/decoding
- `landrok/activitypub` - ActivityPub implementation
- `vlucas/phpdotenv` - Environment configuration

### Autoloading

Follow PSR-4:

```json
"autoload": {
    "psr-4": {
        "nostriphant\\nostripub\\": "src/",
        "nostriphant\\nostripubTests\\": "tests/"
    }
}
```

## Security

### Input Validation

Always validate and sanitize user input:

```php
$requested_resource = $_GET['resource'] ?? '';
if (!preg_match('/^(acct|nostr):.+/', $requested_resource)) {
    header('HTTP/1.1 400 Bad Request', true);
    exit('Bad Request');
}
```

### URL Fetching

Validate URLs before fetching to prevent SSRF:

```php
$parsed = parse_url($profile->picture);
if (!in_array($parsed['scheme'] ?? '', ['http', 'https'], true)) {
    return; // Invalid scheme
}
```

### URL Building

Always use `urlencode()` for query parameters:

```php
header('Location: https://' . $domain . '/.well-known/webfinger?resource=acct:' . urlencode($handle));
```

## Cache

### Directory

Use the `CACHE_DIR` constant defined in bootstrap:

```php
define('CACHE_DIR', __DIR__ . '/cache');
```

### File Naming

Use MD5 hash of URL for cache files:

```php
$cache_file = $this->cache . '/'. md5($url);
```

### Error Caching

Cache failed requests to prevent repeated failures:

```php
if ($info['http_code'] !== 200) {
    touch($cache_file . '.error');
    exit($error('404'));
}
```

## Commits

- Use conventional commit format
- Reference issues where applicable
- Keep commits focused and atomic
