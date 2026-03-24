<?php

declare(strict_types=1);

/**
 * Cleans up old Amavisd quarantine and mail log records.
 * Designed to run as a cron job.
 *
 * Usage: php cli/cleanupAmavisdDb.php [--quarantine-days=7] [--maillog-days=7]
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\Settings;
use App\Repositories\Mysql\MysqlAmavisdRepository;

$settings = Settings::getInstance();

if (!$settings->amavisdEnabled) {
    echo "Amavisd integration is not enabled.\n";
    exit(0);
}

$options = getopt('', ['quarantine-days::', 'maillog-days::']);
$quarantineDays = (int) ($options['quarantine-days'] ?? 7);
$maillogDays = (int) ($options['maillog-days'] ?? 7);

$repo = new MysqlAmavisdRepository();

echo "Cleaning quarantined messages older than {$quarantineDays} days...\n";
$quarantineDeleted = $repo->cleanupQuarantined($quarantineDays);
echo "Deleted: {$quarantineDeleted} records\n";

echo "Cleaning mail log records older than {$maillogDays} days...\n";
$logDeleted = $repo->cleanupMailLog($maillogDays);
echo "Deleted: {$logDeleted} records\n";

echo "Done.\n";
