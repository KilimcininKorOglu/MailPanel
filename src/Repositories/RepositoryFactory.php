<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Settings;
use App\Repositories\Ldap\LdapDashboardRepository;
use App\Repositories\Ldap\LdapDomainAliasRepository;
use App\Repositories\Ldap\LdapAdminRepository;
use App\Repositories\Ldap\LdapAuthRepository;
use App\Repositories\Ldap\LdapDomainRepository;
use App\Repositories\Ldap\LdapForwardingRepository;
use App\Repositories\Ldap\LdapQuotaRepository;
use App\Repositories\Ldap\LdapUserRepository;
use App\Repositories\Mysql\MysqlDashboardRepository;
use App\Repositories\Mysql\MysqlDomainAliasRepository;
use App\Repositories\Mysql\MysqlAdminRepository;
use App\Repositories\Mysql\MysqlAuthRepository;
use App\Repositories\Mysql\MysqlDomainRepository;
use App\Repositories\Mysql\MysqlForwardingRepository;
use App\Repositories\Mysql\MysqlQuotaRepository;
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
    private static ?ForwardingRepositoryInterface $forwardingRepo = null;
    private static ?QuotaRepositoryInterface $quotaRepo = null;
    private static ?DashboardRepositoryInterface $dashboardRepo = null;
    private static ?DomainAliasRepositoryInterface $domainAliasRepo = null;

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

    public static function getForwardingRepository(): ForwardingRepositoryInterface
    {
        if (self::$forwardingRepo === null) {
            self::$forwardingRepo = match (Settings::getInstance()->backend) {
                'mysql' => new MysqlForwardingRepository(),
                default => new LdapForwardingRepository(),
            };
        }
        return self::$forwardingRepo;
    }

    public static function getQuotaRepository(): QuotaRepositoryInterface
    {
        if (self::$quotaRepo === null) {
            self::$quotaRepo = match (Settings::getInstance()->backend) {
                'mysql' => new MysqlQuotaRepository(),
                default => new LdapQuotaRepository(),
            };
        }
        return self::$quotaRepo;
    }

    public static function getDashboardRepository(): DashboardRepositoryInterface
    {
        if (self::$dashboardRepo === null) {
            self::$dashboardRepo = match (Settings::getInstance()->backend) {
                'mysql' => new MysqlDashboardRepository(),
                default => new LdapDashboardRepository(),
            };
        }
        return self::$dashboardRepo;
    }

    public static function getDomainAliasRepository(): DomainAliasRepositoryInterface
    {
        if (self::$domainAliasRepo === null) {
            self::$domainAliasRepo = match (Settings::getInstance()->backend) {
                'mysql' => new MysqlDomainAliasRepository(),
                default => new LdapDomainAliasRepository(),
            };
        }
        return self::$domainAliasRepo;
    }
}
