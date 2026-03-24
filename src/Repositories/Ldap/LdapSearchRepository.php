<?php

declare(strict_types=1);

namespace App\Repositories\Ldap;

use App\Models\LdapConnection;
use App\Models\Settings;
use App\Repositories\SearchRepositoryInterface;

class LdapSearchRepository implements SearchRepositoryInterface
{
    public function search(string $query, array $accountTypes = [], array $statusFilter = [], array $managedDomains = []): array
    {
        $conn = LdapConnection::getInstance()->getConn();
        $settings = Settings::getInstance();
        $baseDn = "o=domains,{$settings->ldapRootDn}";
        $searchAll = empty($accountTypes);
        $safeQuery = ldap_escape($query, '', LDAP_ESCAPE_FILTER);

        $results = [
            'domains' => [],
            'users' => [],
            'aliases' => [],
            'mailingLists' => [],
            'admins' => [],
        ];

        if ($searchAll || in_array('domain', $accountTypes, true)) {
            $results['domains'] = $this->searchLdap($conn, $baseDn,
                "(&(objectClass=mailDomain)(|(domainName=*{$safeQuery}*)))",
                ['domainName', 'accountStatus', 'description'], $managedDomains, 'domain');
        }

        if ($searchAll || in_array('user', $accountTypes, true)) {
            $results['users'] = $this->searchLdap($conn, $baseDn,
                "(&(objectClass=mailUser)(|(mail=*{$safeQuery}*)(cn=*{$safeQuery}*)))",
                ['mail', 'cn', 'accountStatus'], $managedDomains, 'user');
        }

        if ($searchAll || in_array('alias', $accountTypes, true)) {
            $results['aliases'] = $this->searchLdap($conn, $baseDn,
                "(&(objectClass=mailList)(!(enabledService=mlmmj))(|(mail=*{$safeQuery}*)(cn=*{$safeQuery}*)))",
                ['mail', 'cn', 'accountStatus'], $managedDomains, 'alias');
        }

        if ($searchAll || in_array('ml', $accountTypes, true)) {
            $results['mailingLists'] = $this->searchLdap($conn, $baseDn,
                "(&(objectClass=mailList)(enabledService=mlmmj)(|(mail=*{$safeQuery}*)(cn=*{$safeQuery}*)))",
                ['mail', 'cn', 'accountStatus'], $managedDomains, 'ml');
        }

        if (($searchAll || in_array('admin', $accountTypes, true)) && empty($managedDomains)) {
            $adminDn = "ou=Users,domainName=" . ldap_escape($settings->ldapRootDn, '', LDAP_ESCAPE_DN);
            $results['admins'] = $this->searchLdap($conn, $baseDn,
                "(&(objectClass=mailAdmin)(|(mail=*{$safeQuery}*)(cn=*{$safeQuery}*)))",
                ['mail', 'cn', 'accountStatus'], [], 'admin');
        }

        return $results;
    }

    private function searchLdap($conn, string $baseDn, string $filter, array $attrs, array $managedDomains, string $type): array
    {
        $result = @ldap_search($conn, $baseDn, $filter, $attrs, 0, 50);
        if ($result === false) {
            return [];
        }

        $entries = ldap_get_entries($conn, $result);
        $items = [];

        for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
            $entry = $entries[$i];
            $email = $entry['mail'][0] ?? $entry['domainname'][0] ?? '';
            $domain = str_contains($email, '@') ? explode('@', $email, 2)[1] : $email;

            if (!empty($managedDomains) && !in_array($domain, $managedDomains, true)) {
                continue;
            }

            $items[] = [
                'domain' => $domain,
                'username' => $email,
                'address' => $email,
                'name' => $entry['cn'][0] ?? '',
                'description' => $entry['description'][0] ?? '',
                'active' => ($entry['accountstatus'][0] ?? 'active') === 'active' ? 1 : 0,
            ];
        }

        return $items;
    }
}
