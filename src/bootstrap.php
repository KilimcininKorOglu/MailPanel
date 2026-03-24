<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

define('APP_VERSION', '0.1.0');

use App\Models\Settings;
use Dotenv\Dotenv;

// Load environment variables from .env or .env.prod
$dotenv = Dotenv::createImmutable(__DIR__ . '/..', ['.env', '.env.prod']);
$dotenv->safeLoad();

// Start session
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Validate settings eagerly (exit on failure, mirrors Python app.py behavior)
try {
    Settings::getInstance();
} catch (\RuntimeException $e) {
    error_log("Settings validation failed: " . $e->getMessage());
    http_response_code(500);
    echo "Configuration error. Check server logs.";
    exit(1);
}
