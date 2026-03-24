<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware;
use App\Models\LdapConnection;
use App\Models\Settings;
use App\TemplateEngine;
use App\Utils\LdapUtils;

class DomainController
{
    private const DOMAIN_ATTRS = ['domainName', 'accountStatus', 'domainCurrentUserNumber'];

    /**
     * Displays the domain list page.
     */
    public static function domainList(TemplateEngine $tpl): void
    {
        Middleware::loginRequired();

        $conn = LdapConnection::getInstance()->getConn();
        $settings = Settings::getInstance();

        $result = @ldap_search(
            $conn,
            $settings->ldapRootDn,
            '(objectClass=mailDomain)',
            self::DOMAIN_ATTRS
        );

        $domainInfo = [];
        if ($result !== false) {
            $entries = ldap_get_entries($conn, $result);
            for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
                $domainInfo[] = LdapUtils::normalizeEntry($entries[$i], self::DOMAIN_ATTRS);
            }
        }

        $tpl->render('domainList.php', [
            'domainInfo' => $domainInfo,
        ]);
    }
}
