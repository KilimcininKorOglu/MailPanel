<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware;
use App\Models\PaginatedResult;
use App\Models\Settings;
use App\Repositories\Mysql\IredadminConnection;
use App\TemplateEngine;

class LogController
{
    /**
     * Displays the activity log list page.
     */
    public static function logList(TemplateEngine $tpl): void
    {
        Middleware::loginRequired();

        $conn = IredadminConnection::getInstance();
        if (!$conn->isAvailable()) {
            $tpl->render('logList.php', [
                'logs' => [],
                'paginatedResult' => new PaginatedResult([], 0, 1, 50),
                'filterDomain' => '',
                'filterEvent' => '',
                'loggingEnabled' => false,
            ]);
            return;
        }

        $pdo = $conn->getPdo();
        $settings = Settings::getInstance();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = $settings->paginationPerPage;
        $offset = ($page - 1) * $perPage;
        $filterDomain = $_GET['domain'] ?? '';
        $filterEvent = $_GET['event'] ?? '';

        // Build WHERE clause
        $where = '1=1';
        $params = [];

        if ($filterDomain !== '') {
            $where .= ' AND domain = :domain';
            $params['domain'] = $filterDomain;
        }

        if ($filterEvent !== '') {
            $where .= ' AND event = :event';
            $params['event'] = $filterEvent;
        }

        // Count
        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM log WHERE {$where}");
        $countStmt->execute($params);
        $totalCount = (int) $countStmt->fetch()['total'];

        // Fetch
        $stmt = $pdo->prepare(
            "SELECT id, admin, ip, domain, username, event, loglevel, msg, timestamp
             FROM log WHERE {$where}
             ORDER BY timestamp DESC
             LIMIT :perPage OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('perPage', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $logs = $stmt->fetchAll();

        $tpl->render('logList.php', [
            'logs' => $logs,
            'paginatedResult' => new PaginatedResult($logs, $totalCount, $page, $perPage),
            'filterDomain' => $filterDomain,
            'filterEvent' => $filterEvent,
            'loggingEnabled' => true,
        ]);
    }
}
