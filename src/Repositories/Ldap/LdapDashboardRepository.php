<?php

declare(strict_types=1);

namespace App\Repositories\Ldap;

use App\Models\LdapConnection;
use App\Models\Settings;
use App\Repositories\DashboardRepositoryInterface;

class LdapDashboardRepository implements DashboardRepositoryInterface
{
    public function getStats(): array
    {
        $conn = LdapConnection::getInstance()->getConn();
        $settings = Settings::getInstance();
        $rootDn = $settings->ldapRootDn;

        $stats = [
            'totalDomains' => 0,
            'activeDomains' => 0,
            'totalUsers' => 0,
            'activeUsers' => 0,
            'totalAdmins' => 0,
            'totalQuotaAllocated' => 0,
            'totalQuotaUsed' => 0,
            'totalMessages' => 0,
        ];

        // Count domains
        $result = @ldap_search($conn, $rootDn, '(objectClass=mailDomain)', ['domainName']);
        if ($result !== false) {
            $stats['totalDomains'] = ldap_count_entries($conn, $result);
        }

        $result = @ldap_search($conn, $rootDn, '(&(objectClass=mailDomain)(accountStatus=active))', ['domainName']);
        if ($result !== false) {
            $stats['activeDomains'] = ldap_count_entries($conn, $result);
        }

        // Count users
        $result = @ldap_search($conn, $rootDn, '(objectClass=mailUser)', ['uid']);
        if ($result !== false) {
            $stats['totalUsers'] = ldap_count_entries($conn, $result);
        }

        $result = @ldap_search($conn, $rootDn, '(&(objectClass=mailUser)(accountStatus=active))', ['uid']);
        if ($result !== false) {
            $stats['activeUsers'] = ldap_count_entries($conn, $result);
        }

        // Count admins
        $result = @ldap_search($conn, $rootDn, '(&(objectClass=mailUser)(domainGlobalAdmin=yes))', ['mail']);
        if ($result !== false) {
            $stats['totalAdmins'] = ldap_count_entries($conn, $result);
        }

        return $stats;
    }
}
