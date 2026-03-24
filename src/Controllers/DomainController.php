<?php

declare(strict_types=1);

namespace App\Controllers;

use App\CsrfProtection;
use App\Middleware;
use App\Models\Domain;
use App\Models\Settings;
use App\Repositories\RepositoryFactory;
use App\TemplateEngine;

class DomainController
{
    /**
     * Displays the paginated domain list page.
     */
    public static function domainList(TemplateEngine $tpl): void
    {
        Middleware::loginRequired();

        $settings = Settings::getInstance();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = $settings->paginationPerPage;

        $paginatedResult = RepositoryFactory::getDomainRepository()->getDomainsPaginated($page, $perPage);

        $tpl->render('domainList.php', [
            'paginatedResult' => $paginatedResult,
            'domains' => $paginatedResult->items,
        ]);
    }

    /**
     * Displays the domain creation form and handles creation.
     */
    public static function domainCreate(TemplateEngine $tpl): void
    {
        Middleware::loginRequired();

        $error = null;
        $validationErrors = [];
        $domain = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $domain = Domain::fromFormData($_POST);

                if (empty($domain->domainName)) {
                    $validationErrors['domainName'] = 'Domain name is required';
                } elseif (!preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)*\.[a-z]{2,}$/', $domain->domainName)) {
                    $validationErrors['domainName'] = 'Invalid domain name format';
                }

                if (empty($validationErrors)) {
                    $repo = RepositoryFactory::getDomainRepository();

                    // Check for duplicate
                    if ($repo->getDomain($domain->domainName) !== null) {
                        $validationErrors['domainName'] = "Domain '{$domain->domainName}' already exists";
                    } else {
                        $repo->createDomain($domain);
                        header("Location: /domains");
                        exit;
                    }
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        $tpl->render('domainCreate.php', [
            'domain' => $domain,
            'error' => $error,
            'validationErrors' => $validationErrors,
        ]);
    }

    /**
     * Displays and handles domain editing.
     */
    public static function domainView(TemplateEngine $tpl, string $domainName): void
    {
        Middleware::loginRequired();

        $repo = RepositoryFactory::getDomainRepository();
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $domain = Domain::fromFormData($_POST);
                $domain = new Domain(
                    domainName: $domainName,
                    description: $domain->description,
                    active: $domain->active,
                    maxQuota: $domain->maxQuota,
                    quota: $domain->quota,
                    mailboxes: $domain->mailboxes,
                    aliases: $domain->aliases,
                    transport: $domain->transport,
                    settings: $domain->settings,
                );
                $repo->updateDomain($domain);
                $success = 'Domain updated successfully!';
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        $domain = $repo->getDomain($domainName);
        if ($domain === null) {
            http_response_code(404);
            $tpl->render('page404.php');
            return;
        }

        $tpl->render('domainView.php', [
            'domain' => $domain,
            'error' => $error,
            'success' => $success,
        ]);
    }

    /**
     * Handles domain deletion (POST only).
     */
    public static function domainDelete(TemplateEngine $tpl, string $domainName): void
    {
        Middleware::loginRequired();
        CsrfProtection::validateToken();

        try {
            $adminEmail = $_SESSION['email'] ?? '';
            RepositoryFactory::getDomainRepository()->deleteDomain($domainName, $adminEmail);
            header("Location: /domains");
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            $tpl->render('page404.php');
        }
    }
}
