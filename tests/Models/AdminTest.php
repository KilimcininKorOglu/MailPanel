<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Admin;
use PHPUnit\Framework\TestCase;

class AdminTest extends TestCase
{
    public function testDefaultConstructor(): void
    {
        $admin = new Admin(username: 'admin@test.com');

        $this->assertSame('admin@test.com', $admin->username);
        $this->assertSame('', $admin->name);
        $this->assertTrue($admin->active);
        $this->assertFalse($admin->isGlobalAdmin);
        $this->assertFalse($admin->isMailboxAdmin);
        $this->assertNull($admin->created);
    }

    public function testFromFormData(): void
    {
        $post = [
            'username' => '  Admin@Example.COM  ',
            'name' => 'Test Admin',
            'active' => '1',
            'isGlobalAdmin' => '1',
        ];

        $admin = Admin::fromFormData($post);

        $this->assertSame('admin@example.com', $admin->username);
        $this->assertSame('Test Admin', $admin->name);
        $this->assertTrue($admin->active);
        $this->assertTrue($admin->isGlobalAdmin);
    }

    public function testFromFormDataNoCheckboxes(): void
    {
        $post = ['username' => 'admin@test.com', 'name' => 'Test'];

        $admin = Admin::fromFormData($post);

        $this->assertFalse($admin->active);
        $this->assertFalse($admin->isGlobalAdmin);
    }

    public function testFromMysqlRowStandalone(): void
    {
        $row = [
            'username' => 'standalone@test.com',
            'name' => 'Standalone Admin',
            'active' => 1,
            'isGlobalAdmin' => 1,
            'created' => '2026-01-01',
            'passwordlastchange' => '2026-03-01',
        ];

        $admin = Admin::fromMysqlRow($row, false);

        $this->assertSame('standalone@test.com', $admin->username);
        $this->assertSame('Standalone Admin', $admin->name);
        $this->assertTrue($admin->active);
        $this->assertTrue($admin->isGlobalAdmin);
        $this->assertFalse($admin->isMailboxAdmin);
        $this->assertSame('2026-01-01', $admin->created);
    }

    public function testFromMysqlRowMailboxAdmin(): void
    {
        $row = [
            'username' => 'mailbox@test.com',
            'name' => 'Mailbox Admin',
            'active' => 1,
            'isGlobalAdmin' => 0,
        ];

        $admin = Admin::fromMysqlRow($row, true);

        $this->assertTrue($admin->isMailboxAdmin);
        $this->assertFalse($admin->isGlobalAdmin);
    }

    public function testFromLdapEntry(): void
    {
        $entry = [
            'mail' => 'ldap@test.com',
            'cn' => 'LDAP Admin',
            'accountStatus' => 'active',
            'domainGlobalAdmin' => 'yes',
        ];

        $admin = Admin::fromLdapEntry($entry, true);

        $this->assertSame('ldap@test.com', $admin->username);
        $this->assertSame('LDAP Admin', $admin->name);
        $this->assertTrue($admin->active);
        $this->assertTrue($admin->isGlobalAdmin);
        $this->assertTrue($admin->isMailboxAdmin);
    }

    public function testFromLdapEntryDisabled(): void
    {
        $entry = [
            'mail' => 'disabled@test.com',
            'cn' => 'Disabled',
            'accountStatus' => 'disabled',
        ];

        $admin = Admin::fromLdapEntry($entry);

        $this->assertFalse($admin->active);
        $this->assertFalse($admin->isGlobalAdmin);
    }
}
