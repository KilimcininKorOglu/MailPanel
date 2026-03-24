<?php

declare(strict_types=1);

namespace App\Models;

/**
 * User data model. Maps to iRedMail user attributes.
 * mailQuota is always stored in megabytes regardless of backend.
 */
class User
{
    public function __construct(
        public string $uid,
        public bool $accountStatus = false,
        public int $mailQuota = 100,
        public string $cn = '',
        public string $givenName = '',
        public string $sn = '',
        public string $employeeNumber = '',
        public string $title = '',
        public string $mobile = '',
        public string $telephoneNumber = '',
        public bool $domainGlobalAdmin = false,
    ) {}

    /**
     * Creates a User from a normalized LDAP entry array.
     * Converts LDAP quota (bytes) to megabytes at the model boundary.
     */
    public static function fromLdapEntry(array $entry): self
    {
        $quotaBytes = (int) ($entry['mailQuota'] ?? 0);
        $quotaMb = $quotaBytes > 0 ? (int) ($quotaBytes / 1048576) : 0;

        return new self(
            uid: $entry['uid'] ?? '',
            accountStatus: ($entry['accountStatus'] ?? '') === 'active',
            mailQuota: $quotaMb,
            cn: $entry['cn'] ?? '',
            givenName: $entry['givenName'] ?? '',
            sn: $entry['sn'] ?? '',
            employeeNumber: $entry['employeeNumber'] ?? '',
            title: $entry['title'] ?? '',
            mobile: $entry['mobile'] ?? '',
            telephoneNumber: $entry['telephoneNumber'] ?? '',
            domainGlobalAdmin: ($entry['domainGlobalAdmin'] ?? '') === 'yes',
        );
    }

    /**
     * Creates a User from $_POST form data.
     */
    public static function fromFormData(array $post): self
    {
        return new self(
            uid: trim($post['uid'] ?? ''),
            accountStatus: isset($post['accountStatus']),
            mailQuota: (int) ($post['mailQuota'] ?? 100),
            cn: trim($post['cn'] ?? ''),
            givenName: trim($post['givenName'] ?? ''),
            sn: trim($post['sn'] ?? ''),
            employeeNumber: trim($post['employeeNumber'] ?? ''),
            title: trim($post['title'] ?? ''),
            mobile: trim($post['mobile'] ?? ''),
            telephoneNumber: trim($post['telephoneNumber'] ?? ''),
            domainGlobalAdmin: isset($post['domainGlobalAdmin']),
        );
    }
}
