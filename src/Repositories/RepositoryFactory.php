<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Settings;
use App\Repositories\Ldap\LdapAdminRepository;
use App\Repositories\Ldap\LdapAuthRepository;
use App\Repositories\Ldap\LdapDomainRepository;
use App\Repositories\Ldap\LdapUserRepository;
use App\Repositories\Mysql\MysqlAdminRepository;
use App\Repositories\Mysql\MysqlAuthRepository;
use App\Repositories\Mysql\MysqlDomainRepository;
use App\Repositories\Mysql\MysqlUserRepository;

/**
 * Returns the correct repository implementation based on MAILPANEL_BACKEND setting.
 */
class RepositoryFactory
{
    private static ?AuthRepositoryInterface $authRepo = null;
    private static ?DomainRepositoryInterface $domainRepo = null;
    private static ?UserRepositoryInterface $userRepo = null;
    private static ?AdminRepositoryInterface $adminRepo = null;

    public static function getAuthRepository(): AuthRepositoryInterface
    {
        if (self::$authRepo === null) {
            self::$authRepo = match (Settings::getInstance()->backend) {
                'mysql' => new MysqlAuthRepository(),
                default => new LdapAuthRepository(),
            };
        }
        return self::$authRepo;
    }

    public static function getDomainRepository(): DomainRepositoryInterface
    {
        if (self::$domainRepo === null) {
            self::$domainRepo = match (Settings::getInstance()->backend) {
                'mysql' => new MysqlDomainRepository(),
                default => new LdapDomainRepository(),
            };
        }
        return self::$domainRepo;
    }

    public static function getUserRepository(): UserRepositoryInterface
    {
        if (self::$userRepo === null) {
            self::$userRepo = match (Settings::getInstance()->backend) {
                'mysql' => new MysqlUserRepository(),
                default => new LdapUserRepository(),
            };
        }
        return self::$userRepo;
    }

    public static function getAdminRepository(): AdminRepositoryInterface
    {
        if (self::$adminRepo === null) {
            self::$adminRepo = match (Settings::getInstance()->backend) {
                'mysql' => new MysqlAdminRepository(),
                default => new LdapAdminRepository(),
            };
        }
        return self::$adminRepo;
    }
}
