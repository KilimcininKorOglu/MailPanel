<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware;
use App\Models\Settings;
use App\Repositories\RepositoryFactory;
use App\TemplateEngine;

class SystemSettingsController
{
    public static function view(TemplateEngine $tpl): void
    {
        Middleware::globalAdminRequired();

        $settings = Settings::getInstance();

        $tpl->render('systemSettings.php', [
            'backend' => $settings->backend,
            'passwordScheme' => $settings->passwordDefaultScheme,
            'passwordMinLength' => $settings->passwordMinLength,
            'paginationPerPage' => $settings->paginationPerPage,
            'sessionTimeout' => $settings->sessionTimeout,
            'allowedIpRanges' => $settings->allowedIpRanges,
            'sessionValidateIp' => $settings->sessionValidateIp,
            'checkUpdates' => $settings->checkUpdates,
            'amavisdEnabled' => $settings->amavisdEnabled,
            'fail2banEnabled' => $settings->fail2banEnabled,
            'iredapdEnabled' => $settings->iredapdEnabled,
        ]);
    }

    public static function lastLogins(TemplateEngine $tpl): void
    {
        Middleware::globalAdminRequired();

        $settings = Settings::getInstance();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = $settings->paginationPerPage;
        $domainFilter = $_GET['domain'] ?? null;

        $repo = RepositoryFactory::getLastLoginRepository();
        $paginatedResult = $repo->getLastLoginsPaginated($page, $perPage, $domainFilter);
        $domains = RepositoryFactory::getDomainRepository()->getDomains();

        $tpl->render('lastLoginList.php', [
            'logins' => $paginatedResult->items,
            'paginatedResult' => $paginatedResult,
            'domains' => $domains,
            'filterDomain' => $domainFilter ?? '',
        ]);
    }
}
