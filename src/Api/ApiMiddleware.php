<?php

declare(strict_types=1);

namespace App\Api;

use App\Models\Settings;

class ApiMiddleware
{
    public static function authenticate(): void
    {
        $settings = Settings::getInstance();

        if (!$settings->apiEnabled) {
            ApiResponse::error('API is not enabled', 403);
            exit;
        }

        $apiKey = self::getApiKeyFromRequest();

        if ($apiKey === '' || $settings->apiKey === '') {
            ApiResponse::error('Unauthorized', 401);
            exit;
        }

        if (!hash_equals($settings->apiKey, $apiKey)) {
            ApiResponse::error('Invalid API key', 401);
            exit;
        }

        $allowedIps = $settings->apiAllowedIps;
        if ($allowedIps !== '') {
            $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
            $allowed = array_filter(array_map('trim', explode(',', $allowedIps)));

            if (!empty($allowed) && !in_array($clientIp, $allowed, true)) {
                ApiResponse::error('IP not allowed', 403);
                exit;
            }
        }
    }

    private static function getApiKeyFromRequest(): string
    {
        $header = $_SERVER['HTTP_X_API_KEY'] ?? '';
        if ($header !== '') {
            return $header;
        }

        return $_GET['apiKey'] ?? '';
    }

    public static function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === '' || $raw === false) {
            return [];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
