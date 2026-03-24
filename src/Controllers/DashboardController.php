<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware;
use App\Repositories\RepositoryFactory;
use App\TemplateEngine;

class DashboardController
{
    /**
     * Displays the dashboard with statistics.
     */
    public static function dashboard(TemplateEngine $tpl): void
    {
        Middleware::loginRequired();

        $stats = RepositoryFactory::getDashboardRepository()->getStats();

        $tpl->render('dashboard.php', [
            'stats' => $stats,
        ]);
    }
}
