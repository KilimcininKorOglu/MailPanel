<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware;
use App\Models\Settings;
use App\Repositories\RepositoryFactory;
use App\Services\ActivityLogger;
use App\TemplateEngine;

class IredapdController
{
    public static function throttleView(TemplateEngine $tpl, string $account): void
    {
        Middleware::globalAdminRequired();
        self::requireEnabled();

        $repo = RepositoryFactory::getIredapdRepository();
        $success = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $kind = $_POST['kind'] ?? 'outbound';
                $period = (int) ($_POST['period'] ?? 3600);
                $maxMsgs = (int) ($_POST['maxMsgs'] ?? 0);
                $maxQuota = (int) ($_POST['maxQuota'] ?? 0);
                $msgSize = (int) ($_POST['msgSize'] ?? 0);

                $repo->setThrottleSettings($account, $kind, $period, $maxMsgs, $maxQuota, $msgSize);
                ActivityLogger::logUpdate('', $account, "Throttle settings updated for {$account}");
                $success = 'Throttle settings updated!';
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        $throttleSettings = $repo->getThrottleSettings($account);

        $tpl->render('throttleView.php', [
            'account' => $account,
            'throttleSettings' => $throttleSettings,
            'success' => $success,
            'error' => $error,
        ]);
    }

    public static function greylistView(TemplateEngine $tpl, string $account): void
    {
        Middleware::globalAdminRequired();
        self::requireEnabled();

        $repo = RepositoryFactory::getIredapdRepository();
        $success = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $action = $_POST['action'] ?? '';

                if ($action === 'toggle') {
                    $enabled = isset($_POST['enabled']);
                    $repo->setGreylistEnabled($account, $enabled);
                    $status = $enabled ? 'enabled' : 'disabled';
                    ActivityLogger::logUpdate('', $account, "Greylisting {$status} for {$account}");
                    $success = "Greylisting {$status}!";
                } elseif ($action === 'whitelist') {
                    $sendersRaw = $_POST['whitelistedSenders'] ?? '';
                    $senders = array_filter(array_map('trim', explode("\n", $sendersRaw)));
                    $repo->setWhitelistedSenders($account, $senders);
                    ActivityLogger::logUpdate('', $account, "Greylist whitelist updated for {$account}");
                    $success = 'Whitelist updated!';
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        $greylistSettings = $repo->getGreylistSettings($account);
        $whitelistedSenders = $repo->getWhitelistedSenders($account);
        $greylistEnabled = false;
        foreach ($greylistSettings as $setting) {
            if (($setting['sender'] ?? '') === '@.' && ($setting['active'] ?? 0)) {
                $greylistEnabled = true;
                break;
            }
        }

        $tpl->render('greylistView.php', [
            'account' => $account,
            'greylistEnabled' => $greylistEnabled,
            'whitelistedSenders' => $whitelistedSenders,
            'success' => $success,
            'error' => $error,
        ]);
    }

    private static function requireEnabled(): void
    {
        if (!Settings::getInstance()->iredapdEnabled) {
            http_response_code(403);
            echo 'iRedAPD integration is not enabled';
            exit;
        }
    }
}
