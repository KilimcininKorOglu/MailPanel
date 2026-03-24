<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware;
use App\Repositories\RepositoryFactory;
use App\TemplateEngine;

class SearchController
{
    public static function search(TemplateEngine $tpl): void
    {
        Middleware::loginRequired();

        $query = trim($_REQUEST['q'] ?? $_REQUEST['searchString'] ?? '');
        $accountTypes = $_REQUEST['accountType'] ?? [];
        $statusFilter = $_REQUEST['accountStatus'] ?? [];

        if (is_string($accountTypes)) {
            $accountTypes = $accountTypes !== '' ? [$accountTypes] : [];
        }
        if (is_string($statusFilter)) {
            $statusFilter = $statusFilter !== '' ? [$statusFilter] : [];
        }

        $results = null;
        if ($query !== '') {
            $managedDomains = [];
            if (empty($_SESSION['isGlobalAdmin'])) {
                $managedDomains = $_SESSION['managedDomains'] ?? [];
            }

            $repo = RepositoryFactory::getSearchRepository();
            $results = $repo->search($query, $accountTypes, $statusFilter, $managedDomains);
        }

        $tpl->render('search.php', [
            'query' => $query,
            'accountTypes' => $accountTypes,
            'statusFilter' => $statusFilter,
            'results' => $results,
        ]);
    }
}
