<?php

declare(strict_types=1);

/**
 * CLI bootstrap — loads autoloader and environment without starting a session.
 * Usage: require_once __DIR__ . '/bootstrap.php';
 */

if (php_sapi_name() !== 'cli') {
    echo "This script can only be run from the command line.\n";
    exit(1);
}

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..', ['.env', '.env.prod']);
$dotenv->safeLoad();

// Validate settings
try {
    \App\Models\Settings::getInstance();
} catch (\RuntimeException $e) {
    echo "Configuration error: " . $e->getMessage() . "\n";
    exit(1);
}
