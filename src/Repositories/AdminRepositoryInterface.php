<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Admin;

interface AdminRepositoryInterface
{
    /**
     * Returns all admin accounts (standalone + mailbox-based admins).
     *
     * @return Admin[]
     */
    public function getAdmins(): array;

    /**
     * Returns a single admin by username, or null if not found.
     */
    public function getAdmin(string $username): ?Admin;

    /**
     * Creates a new standalone admin account.
     */
    public function createAdmin(Admin $admin, string $passwordHash): void;

    /**
     * Updates admin profile fields (name, active status).
     */
    public function updateAdmin(Admin $admin): void;

    /**
     * Updates an admin's password.
     */
    public function updateAdminPassword(string $username, string $passwordHash): void;

    /**
     * Deletes an admin account.
     */
    public function deleteAdmin(string $username): void;

    /**
     * Returns domain names managed by a specific admin.
     *
     * @return string[]
     */
    public function getManagedDomains(string $adminUsername): array;

    /**
     * Assigns a domain to an admin for management.
     */
    public function assignDomainToAdmin(string $adminUsername, string $domain): void;

    /**
     * Revokes a domain assignment from an admin.
     */
    public function revokeDomainFromAdmin(string $adminUsername, string $domain): void;

    /**
     * Enables or disables an admin account.
     */
    public function enableDisableAdmin(string $username, bool $active): void;

    /**
     * Updates admin resource limits stored in settings JSON.
     */
    public function updateAdminSettings(string $username, string $settingsJson): void;

    /**
     * Returns paginated admin list.
     */
    public function getAdminsPaginated(int $page, int $perPage): \App\Models\PaginatedResult;

    /**
     * Counts domains managed by an admin.
     */
    public function countManagedDomains(string $adminUsername): int;

    /**
     * Counts active global admin accounts.
     */
    public function countGlobalAdmins(): int;
}
