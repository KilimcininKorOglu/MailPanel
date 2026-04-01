<?php

declare(strict_types=1);

namespace App\Api;

use App\Models\Admin;
use App\Repositories\RepositoryFactory;
use App\Utils\PasswordUtils;

class AdminApiController
{
    public static function list(): void
    {
        ApiMiddleware::requireGlobalKey();
        $repo = RepositoryFactory::getAdminRepository();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) ($_GET['perPage'] ?? 50);

        $result = $repo->getAdminsPaginated($page, $perPage);
        ApiResponse::paginated($result, fn(Admin $a) => [
            'email' => $a->email,
            'name' => $a->name,
            'isGlobalAdmin' => $a->isGlobalAdmin,
            'active' => $a->active,
        ]);
    }

    public static function get(string $email): void
    {
        ApiMiddleware::requireGlobalKey();
        $admin = RepositoryFactory::getAdminRepository()->getAdmin($email);
        if ($admin === null) {
            ApiResponse::error('Admin not found', 404);
            return;
        }
        ApiResponse::success((array) $admin);
    }

    public static function create(): void
    {
        ApiMiddleware::requireGlobalKey();
        ApiMiddleware::requireWriteAccess();
        $data = ApiMiddleware::getJsonBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if ($email === '' || $password === '') {
            ApiResponse::error('email and password are required');
            return;
        }

        $repo = RepositoryFactory::getAdminRepository();
        if ($repo->getAdmin($email) !== null) {
            ApiResponse::error('Admin already exists', 409);
            return;
        }

        $passwordHash = PasswordUtils::generatePasswordHash($password);
        $repo->createAdmin($email, $passwordHash, $data['name'] ?? '', $data['isGlobalAdmin'] ?? false);
        ApiResponse::created(['email' => $email]);
    }

    public static function update(string $email): void
    {
        ApiMiddleware::requireGlobalKey();
        ApiMiddleware::requireWriteAccess();
        $repo = RepositoryFactory::getAdminRepository();
        $admin = $repo->getAdmin($email);
        if ($admin === null) {
            ApiResponse::error('Admin not found', 404);
            return;
        }

        $data = ApiMiddleware::getJsonBody();

        if (isset($data['password'])) {
            $passwordHash = PasswordUtils::generatePasswordHash($data['password']);
            $repo->updateAdminPassword($email, $passwordHash);
        }

        if (isset($data['active'])) {
            $repo->enableDisableAdmin($email, (bool) $data['active']);
        }

        ApiResponse::success(['message' => 'Admin updated']);
    }

    public static function delete(string $email): void
    {
        ApiMiddleware::requireGlobalKey();
        ApiMiddleware::requireWriteAccess();
        $repo = RepositoryFactory::getAdminRepository();
        $admin = $repo->getAdmin($email);
        if ($admin === null) {
            ApiResponse::error('Admin not found', 404);
            return;
        }

        // Prevent last global admin deletion
        if ($admin->isGlobalAdmin && $repo->countGlobalAdmins() <= 1) {
            ApiResponse::error('Cannot delete the last global admin account', 403);
            return;
        }

        $repo->deleteAdmin($email);
        ApiResponse::deleted();
    }
}
