<?php

declare(strict_types=1);

namespace App\Controllers;

use App\CsrfProtection;
use App\Middleware;
use App\Models\Settings;
use App\Services\ActivityLogger;
use App\Services\Fail2banService;
use App\Services\GeoIpService;
use App\TemplateEngine;

class Fail2banController
{
    public static function status(TemplateEngine $tpl): void
    {
        Middleware::globalAdminRequired();
        self::requireEnabled();

        $bannedIps = Fail2banService::getAllBannedIps();
        $jails = Fail2banService::getJails();

        $geoIp = GeoIpService::getInstance();
        $geoData = [];
        if ($geoIp->isAvailable()) {
            foreach ($bannedIps as $entry) {
                $ip = $entry['ip'] ?? '';
                if ($ip !== '' && !isset($geoData[$ip])) {
                    $geoData[$ip] = $geoIp->lookup($ip);
                }
            }
        }

        $tpl->render('fail2banStatus.php', [
            'bannedIps' => $bannedIps,
            'jails' => $jails,
            'geoData' => $geoData,
            'geoIpAvailable' => $geoIp->isAvailable(),
        ]);
    }

    public static function banIp(TemplateEngine $tpl): void
    {
        Middleware::globalAdminRequired();
        CsrfProtection::validateToken();
        self::requireEnabled();

        $jail = $_POST['jail'] ?? '';
        $ip = $_POST['ip'] ?? '';

        $allowedJails = Fail2banService::getJails();
        if (!empty($jail) && !empty($ip) && filter_var($ip, FILTER_VALIDATE_IP) && in_array($jail, $allowedJails, true)) {
            try {
                Fail2banService::banIp($jail, $ip);
                ActivityLogger::log('update', '', '', "Banned IP {$ip} in jail {$jail}");
            } catch (\Exception $e) {
                error_log("Fail2ban ban failed: " . $e->getMessage());
            }
        }

        header("Location: /fail2ban");
        exit;
    }

    public static function unbanIp(TemplateEngine $tpl): void
    {
        Middleware::globalAdminRequired();
        CsrfProtection::validateToken();
        self::requireEnabled();

        $jail = $_POST['jail'] ?? '';
        $ip = $_POST['ip'] ?? '';

        if (!empty($jail) && !empty($ip)) {
            try {
                Fail2banService::unbanIp($jail, $ip);
                ActivityLogger::log('update', '', '', "Unbanned IP {$ip} from jail {$jail}");
            } catch (\Exception $e) {
                error_log("Fail2ban unban failed: " . $e->getMessage());
            }
        }

        header("Location: /fail2ban");
        exit;
    }

    private static function requireEnabled(): void
    {
        if (!Settings::getInstance()->fail2banEnabled) {
            http_response_code(403);
            echo 'Fail2ban integration is not enabled';
            exit;
        }
    }
}
