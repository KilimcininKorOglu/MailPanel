<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Returns a User by uid within a domain, or null if not found.
     */
    public function getUser(string $domain, string $userId): ?User;

    /**
     * Returns all Users for a domain (excluding catch-all entries).
     *
     * @return User[]
     */
    public function getUsers(string $domain): array;

    /**
     * Updates a user's profile fields (everything except password).
     */
    public function updateUser(string $domain, User $user): void;

    /**
     * Updates a user's password with a pre-hashed value.
     */
    public function updateUserPassword(string $domain, string $userUid, string $passwordHash): void;

    /**
     * Creates a new user.
     *
     * @throws \RuntimeException if the backend does not support user creation
     */
    public function createUser(string $domain, User $user, string $passwordHash): void;

    /**
     * Whether this backend supports user creation.
     */
    public function supportsCreateUser(): bool;
}
