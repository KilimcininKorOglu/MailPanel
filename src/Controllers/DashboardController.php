<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware;
use App\Repositories\RepositoryFactory;
use App\Services\VersionChecker;
use App\TemplateEngine;
use App\Utils\SystemInfo;

class DashboardController
{
    /**
     * Displays the dashboard with statistics.
     */
    public static function dashboard(TemplateEngine $tpl): void
    {
        Middleware::loginRequired();

        $stats = RepositoryFactory::getDashboardRepository()->getStats();

        $systemInfo = null;
        $newVersion = null;
        if (Middleware::isGlobalAdmin()) {
            $systemInfo = [
                'hostname' => SystemInfo::getHostname(),
                'uptime' => SystemInfo::getUptime(),
                'loadAverage' => SystemInfo::getLoadAverage(),
                'iredmailVersion' => SystemInfo::getIredMailVersion(),
                'phpVersion' => SystemInfo::getPhpVersion(),
                'mailpanelVersion' => SystemInfo::getMailPanelVersion(),
            ];
            $newVersion = VersionChecker::checkForUpdate();
        }

        $tpl->render('dashboard.php', [
            'stats' => $stats,
            'systemInfo' => $systemInfo,
            'newVersion' => $newVersion,
        ]);
    }
}
