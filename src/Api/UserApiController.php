<?php

declare(strict_types=1);

namespace App\Api;

use App\Models\User;
use App\Repositories\RepositoryFactory;
use App\Utils\PasswordUtils;

class UserApiController
{
    public static function list(string $domain): void
    {
        $repo = RepositoryFactory::getUserRepository();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) ($_GET['perPage'] ?? 50);

        $result = $repo->getUsersPaginated($domain, $page, $perPage);
        ApiResponse::paginated($result, fn(User $u) => [
            'uid' => $u->uid,
            'email' => $u->uid . '@' . $domain,
            'name' => $u->cn,
            'mailQuota' => $u->mailQuota,
            'active' => $u->accountStatus,
        ]);
    }

    public static function get(string $email): void
    {
        [$uid, $domain] = self::parseEmail($email);
        if ($uid === null) {
            ApiResponse::error('Invalid email format');
            return;
        }

        $user = RepositoryFactory::getUserRepository()->getUser($domain, $uid);
        if ($user === null) {
            ApiResponse::error('User not found', 404);
            return;
        }

        ApiResponse::success((array) $user);
    }

    public static function create(string $domain): void
    {
        $data = ApiMiddleware::getJsonBody();
        $repo = RepositoryFactory::getUserRepository();

        if (!$repo->supportsCreateUser()) {
            ApiResponse::error('User creation not supported for this backend', 501);
            return;
        }

        $user = User::fromFormData($data);
        $password = $data['password'] ?? '';

        if ($user->uid === '' || $password === '') {
            ApiResponse::error('uid and password are required');
            return;
        }

        $passwordHash = PasswordUtils::generatePasswordHash($password);
        $repo->createUser($domain, $user, $passwordHash);
        ApiResponse::created(['email' => $user->uid . '@' . $domain]);
    }

    public static function update(string $email): void
    {
        [$uid, $domain] = self::parseEmail($email);
        if ($uid === null) {
            ApiResponse::error('Invalid email format');
            return;
        }

        $repo = RepositoryFactory::getUserRepository();
        $existing = $repo->getUser($domain, $uid);
        if ($existing === null) {
            ApiResponse::error('User not found', 404);
            return;
        }

        $data = ApiMiddleware::getJsonBody();

        if (isset($data['password'])) {
            $passwordHash = PasswordUtils::generatePasswordHash($data['password']);
            $repo->updateUserPassword($domain, $uid, $passwordHash);
        }

        $user = User::fromFormData(array_merge((array) $existing, $data));
        $user->uid = $uid;
        $repo->updateUser($domain, $user);
        ApiResponse::success(['message' => 'User updated']);
    }

    public static function delete(string $email): void
    {
        [$uid, $domain] = self::parseEmail($email);
        if ($uid === null) {
            ApiResponse::error('Invalid email format');
            return;
        }

        $repo = RepositoryFactory::getUserRepository();
        if ($repo->getUser($domain, $uid) === null) {
            ApiResponse::error('User not found', 404);
            return;
        }

        $repo->deleteUser($domain, $uid, 'api');
        ApiResponse::deleted();
    }

    /** @return array{?string, ?string} */
    private static function parseEmail(string $email): array
    {
        if (!str_contains($email, '@')) {
            return [null, null];
        }
        [$uid, $domain] = explode('@', $email, 2);
        return [$uid, $domain];
    }
}
