<?php

declare(strict_types=1);

namespace App\Repositories;

interface PanelSettingsRepositoryInterface
{
    /**
     * Returns all panel settings as an associative array [key => value].
     *
     * @return array<string, string>
     */
    public function getAll(): array;

    /**
     * Returns a single setting value, or null if not found.
     */
    public function get(string $key): ?string;

    /**
     * Sets a single setting value. Inserts or updates.
     */
    public function set(string $key, string $value, string $updatedBy = ''): void;

    /**
     * Sets multiple settings at once. Inserts or updates each.
     *
     * @param array<string, string> $settings
     */
    public function setMany(array $settings, string $updatedBy = ''): void;

    /**
     * Deletes a setting (reverts to .env / default).
     */
    public function delete(string $key): void;

    /**
     * Ensures the panel_settings table exists in the iredadmin database.
     */
    public function ensureTableExists(): void;
}
