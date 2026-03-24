<?php

declare(strict_types=1);

namespace App\Repositories\Ldap;

use App\Models\LdapConnection;
use App\Models\Settings;
use App\Repositories\DomainRepositoryInterface;
use App\Utils\LdapUtils;

class LdapDomainRepository implements DomainRepositoryInterface
{
    private const DOMAIN_ATTRS = ['domainName', 'accountStatus', 'domainCurrentUserNumber'];

    public function getDomains(): array
    {
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

        return $domainInfo;
    }
}
