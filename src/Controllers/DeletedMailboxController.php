<?php

declare(strict_types=1);

namespace App\Controllers;

use App\CsrfProtection;
use App\Middleware;
use App\Models\Settings;
use App\Repositories\RepositoryFactory;
use App\Services\ActivityLogger;
use App\TemplateEngine;

class DeletedMailboxController
{
    /**
     * Displays the list of pending mailbox deletions.
     */
    public static function list(TemplateEngine $tpl): void
    {
        Middleware::globalAdminRequired();

        $settings = Settings::getInstance();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = $settings->paginationPerPage;

        $repo = RepositoryFactory::getDeletedMailboxRepository();
        $paginatedResult = $repo->getPendingDeletions($page, $perPage);

        $tpl->render('deletedMailboxList.php', [
            'deletedMailboxes' => $paginatedResult->items,
            'paginatedResult' => $paginatedResult,
        ]);
    }

    /**
     * Cancels a pending mailbox deletion.
     */
    public static function cancel(TemplateEngine $tpl, string $id): void
    {
        Middleware::globalAdminRequired();
        CsrfProtection::validateToken();

        try {
            $repo = RepositoryFactory::getDeletedMailboxRepository();
            $repo->cancelDeletion((int) $id);
            ActivityLogger::log('update', '', '', "Cancelled mailbox deletion #{$id}");
            header("Location: /deleted-mailboxes");
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            $tpl->render('page404.php');
        }
    }

    /**
     * Reschedules a pending mailbox deletion.
     */
    public static function reschedule(TemplateEngine $tpl, string $id): void
    {
        Middleware::globalAdminRequired();
        CsrfProtection::validateToken();

        $newDate = $_POST['newDate'] ?? '';
        if (empty($newDate)) {
            header("Location: /deleted-mailboxes");
            exit;
        }

        try {
            $repo = RepositoryFactory::getDeletedMailboxRepository();
            $repo->reschedule((int) $id, $newDate);
            ActivityLogger::log('update', '', '', "Rescheduled mailbox deletion #{$id} to {$newDate}");
            header("Location: /deleted-mailboxes");
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            $tpl->render('page404.php');
        }
    }
}
