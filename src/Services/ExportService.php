<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\RepositoryFactory;

class ExportService
{
    public static function exportDomainUsers(string $domain, string $format = 'csv'): void
    {
        $userRepo = RepositoryFactory::getUserRepository();
        $users = $userRepo->getUsers($domain);

        if ($format === 'json') {
            self::sendJsonHeaders("users-{$domain}.json");

            $data = [];
            foreach ($users as $user) {
                $data[] = [
                    'uid' => $user->uid,
                    'email' => $user->uid . '@' . $domain,
                    'name' => $user->name,
                    'mailQuota' => $user->mailQuota,
                    'active' => $user->active,
                ];
            }
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            self::sendCsvHeaders("users-{$domain}.csv");

            $out = fopen('php://output', 'w');
            fputcsv($out, ['UID', 'Email', 'Name', 'Quota (MB)', 'Active']);

            foreach ($users as $user) {
                fputcsv($out, [
                    $user->uid,
                    $user->uid . '@' . $domain,
                    $user->name,
                    $user->mailQuota,
                    $user->active ? 'Yes' : 'No',
                ]);
            }
            fclose($out);
        }
    }

    public static function exportAdminStats(string $format = 'csv'): void
    {
        $adminRepo = RepositoryFactory::getAdminRepository();
        $domainRepo = RepositoryFactory::getDomainRepository();

        $domains = $domainRepo->getDomains();

        if ($format === 'json') {
            self::sendJsonHeaders('admin-stats.json');

            $data = [
                'totalDomains' => count($domains),
                'domains' => [],
            ];

            $userRepo = RepositoryFactory::getUserRepository();
            foreach ($domains as $d) {
                $domainName = $d['domain'] ?? $d['name'] ?? '';
                $users = $userRepo->getUsers($domainName);
                $data['domains'][] = [
                    'domain' => $domainName,
                    'userCount' => count($users),
                ];
            }

            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            self::sendCsvHeaders('admin-stats.csv');

            $out = fopen('php://output', 'w');
            fputcsv($out, ['Domain', 'User Count']);

            $userRepo = RepositoryFactory::getUserRepository();
            foreach ($domains as $d) {
                $domainName = $d['domain'] ?? $d['name'] ?? '';
                $users = $userRepo->getUsers($domainName);
                fputcsv($out, [$domainName, count($users)]);
            }
            fclose($out);
        }
    }

    private static function sendCsvHeaders(string $filename): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: no-cache, no-store, must-revalidate');
    }

    private static function sendJsonHeaders(string $filename): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: no-cache, no-store, must-revalidate');
    }
}
