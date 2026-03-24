<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Set minimal env vars required for Settings singleton in tests
$_ENV['MAILPANEL_BACKEND'] = 'ldap';
$_ENV['MAILPANEL_SECRET_KEY'] = 'test-secret';
$_ENV['MAILPANEL_LDAP_URI'] = 'ldap://localhost';
$_ENV['MAILPANEL_LDAP_ROOT_DN'] = 'dc=test,dc=com';
$_ENV['MAILPANEL_LDAP_USER'] = 'admin@test.com';
$_ENV['MAILPANEL_LDAP_PASSWORD'] = 'test';
