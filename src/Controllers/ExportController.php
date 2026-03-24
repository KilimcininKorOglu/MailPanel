<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware;
use App\Services\ExportService;

class ExportController
{
    public static function domainExport(string $domain): void
    {
        Middleware::domainAdminRequired($domain);

        $format = $_GET['format'] ?? 'csv';
        ExportService::exportDomainUsers($domain, $format);
        exit;
    }

    public static function adminStats(): void
    {
        Middleware::globalAdminRequired();

        $format = $_GET['format'] ?? 'csv';
        ExportService::exportAdminStats($format);
        exit;
    }
}
