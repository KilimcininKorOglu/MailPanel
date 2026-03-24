<?php

declare(strict_types=1);

namespace App\Repositories\Ldap;

use App\Models\LdapConnection;
use App\Repositories\AuthRepositoryInterface;

class LdapAuthRepository implements AuthRepositoryInterface
{
    public function authenticate(string $email, string $password): bool
    {
        LdapConnection::connect($email, $password);
        return true;
    }
}
