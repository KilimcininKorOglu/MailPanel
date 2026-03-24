<?php

declare(strict_types=1);

/**
 * Promotes a mail user to global admin.
 *
 * Usage: php cli/promoteToGlobalAdmin.php --email=admin@example.com
 */

require_once __DIR__ . '/bootstrap.php';

use App\Repositories\RepositoryFactory;

$options = getopt('', ['email:']);

if (empty($options['email'])) {
    echo "Usage: php cli/promoteToGlobalAdmin.php --email=admin@example.com\n";
    exit(1);
}

$email = $options['email'];

if (!str_contains($email, '@')) {
    echo "Invalid email address: {$email}\n";
    exit(1);
}

[$uid, $domain] = explode('@', $email, 2);

$userRepo = RepositoryFactory::getUserRepository();
$user = $userRepo->getUser($domain, $uid);

if ($user === null) {
    echo "User not found: {$email}\n";
    exit(1);
}

if ($user->domainGlobalAdmin) {
    echo "User {$email} is already a global admin.\n";
    exit(0);
}

$user->domainGlobalAdmin = true;
$userRepo->updateUser($domain, $user);

echo "User {$email} has been promoted to global admin.\n";
