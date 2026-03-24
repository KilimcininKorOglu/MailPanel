<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware;
use App\Repositories\RepositoryFactory;
use App\TemplateEngine;

class DomainController
{
    /**
     * Displays the domain list page.
     */
    public static function domainList(TemplateEngine $tpl): void
    {
        Middleware::loginRequired();

        $domainInfo = RepositoryFactory::getDomainRepository()->getDomains();

        $tpl->render('domainList.php', [
            'domainInfo' => $domainInfo,
        ]);
    }
}
