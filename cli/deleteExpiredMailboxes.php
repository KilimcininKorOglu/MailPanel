<?php

declare(strict_types=1);

/**
 * Deletes mailbox directories for expired deleted_mailboxes records.
 * Designed to run as a cron job.
 *
 * Usage: php cli/deleteExpiredMailboxes.php [--dry-run]
 */

require_once __DIR__ . '/bootstrap.php';

use App\Repositories\Mysql\MysqlConnection;

$options = getopt('', ['dry-run']);
$dryRun = isset($options['dry-run']);

if ($dryRun) {
    echo "DRY RUN — no files will be deleted.\n\n";
}

$pdo = MysqlConnection::getInstance()->getPdo();

$stmt = $pdo->query(
    "SELECT id, username, maildir, domain, admin, delete_date
     FROM deleted_mailboxes
     WHERE delete_date IS NOT NULL AND delete_date <= NOW()
     ORDER BY delete_date"
);

$processed = 0;
$errors = 0;

while ($row = $stmt->fetch()) {
    $id = (int) $row['id'];
    $maildir = $row['maildir'];
    $username = $row['username'];

    echo "Processing: {$username} — {$maildir}\n";

    if (!is_dir($maildir)) {
        echo "  Directory not found, removing record.\n";
        if (!$dryRun) {
            $delStmt = $pdo->prepare("DELETE FROM deleted_mailboxes WHERE id = :id");
            $delStmt->execute(['id' => $id]);
        }
        $processed++;
        continue;
    }

    // Safety: verify the directory is within the vmail base path
    $vmailBase = realpath(\App\Models\Settings::getInstance()->vmailPath);
    $resolvedMaildir = realpath($maildir);
    if ($vmailBase === false || $resolvedMaildir === false
        || !str_starts_with($resolvedMaildir, $vmailBase . DIRECTORY_SEPARATOR)) {
        echo "  Path outside vmail base directory, skipping.\n";
        $errors++;
        continue;
    }

    if ($dryRun) {
        echo "  Would delete: {$maildir}\n";
    } else {
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($maildir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }
            rmdir($maildir);
            echo "  Deleted directory.\n";

            $delStmt = $pdo->prepare("DELETE FROM deleted_mailboxes WHERE id = :id");
            $delStmt->execute(['id' => $id]);
        } catch (\Exception $e) {
            echo "  Error: {$e->getMessage()}\n";
            $errors++;
            continue;
        }
    }

    $processed++;
}

echo "\nDone. Processed: {$processed}, Errors: {$errors}\n";
