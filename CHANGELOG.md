# Changelog

All notable changes to this project will be documented in this file.

## [0.9.0] - 2026-03-24

### Added
- Domain CRUD with create, edit, delete, and pagination
- Admin account management with standalone and mailbox-based admin support
- User deletion with deferred mailbox retention
- Pagination across all list views (configurable via MAILPANEL_PAGINATION_PER_PAGE)
- Mail service toggles (SMTP, POP3, IMAP, ManageSieve, SOGo + secured variants)
- Email forwarding management with keep-copy toggle
- Used quota display on user list and detail views
- Bulk user operations (enable/disable/delete)
- LDAP user creation (previously threw RuntimeException)
- Dashboard with domain/user/admin/quota statistics
- Activity logging to iRedAdmin database with admin/IP/event tracking
- Role-based access control (global admin vs domain admin)
- Domain admin login support
- Session timeout and IP restriction (CIDR notation)
- Activity log viewer with domain/event filters
- Deferred mailbox deletion with cancel/reschedule
- Domain settings (default quota, password policies, disclaimer)
- Branding support (name, logo, footer text, primary color)
- CLI tools: bulkPasswordUpdate, bulkQuotaUpdate, deleteExpiredMailboxes, exportUsers, promoteToGlobalAdmin
- Amavisd integration: quarantine viewer, release, mail log, cleanup
- Fail2ban integration: jail status, ban/unban management
- iRedAPD integration: throttle settings, greylisting, sender whitelist
- Feature flags for external integrations in navigation
- Old password verification on password change (configurable)
- Random password generator with policy compliance (server + client-side)
- Status filter tabs (All/Active/Disabled) on domain and user lists
- Log entry deletion (individual and bulk)
- Session IP change detection (MAILPANEL_SESSION_VALIDATE_IP)
- Failed login attempt tracking with activity logging
- Bulk domain operations (enable/disable/delete)
- Bulk admin operations (enable/disable/delete)
- Dashboard system information (hostname, uptime, load, software versions)
- GitHub version check with 24-hour cache (MAILPANEL_CHECK_UPDATES)
- Domain alias management with full CRUD (alias_domain table / LDAP domainAliasName)
- CLI: dumpDisclaimer (Postfix integration), dumpQuarantinedMails, invalidateSessions

### Changed
- Root redirect changed from /domains to /dashboard
- README comprehensively rewritten to document all features

### Fixed
- RBAC enforcement in UserController (domain admins restricted to managed domains)
- Activity logging added to all user operations
- Amavisd quarantine release via amavisd-release CLI
- Navigation links for Deleted Mailboxes and iRedAPD
- Configurable Amavisd cleanup retention days
- Domain deletion now cleans up alias_domain references
- Repository interfaces added for Amavisd and iRedAPD

## [0.3.0] - 2026-03-24

### Added
- PHPUnit test infrastructure with unit tests for PasswordUtils and UserPassword

### Changed
- APP_VERSION now read from Composer metadata instead of hardcoded constant
- Introduced shared `BackendConnectionException` base class for unified error handling
- Suppressed deprecated `crypt()` notices with documentation
- Documented `extract()` usage and EXTR_SKIP safety in template engine
- Removed unused `getUserDn` and `isSupportedPasswordScheme` methods
- Removed unused `name` and `templatesAutoReload` settings
- Removed stale Python reference from route comments
- Removed unimplemented CSV import button from user list

### Fixed
- Null-safe operator in user creation template prevents crash on new form
- Inverted prefix condition in doveadm password hash generation
- Login form hidden `next` field now has name attribute for redirect
- Zero quota preserved as unlimited instead of defaulting to 100 MB
- Aligned quota handling across backends for unlimited accounts
- LDAP modify operations now check return values and throw on failure
- Null-coalescing operator used for env var lookup to handle "0" values correctly
- Router returns 405 when URL matches but HTTP method does not
- `editMode` parameter validated; unknown values return 404
- Mail storage paths configurable for MySQL user creation
- User creation UI hidden when backend does not support it

### Security
- CSRF token generation and validation for all POST forms
- Session ID regenerated after successful authentication
- Redirect target validated in login to prevent open redirect
- Route uid enforced over form body in user edit
- Secure flag added to session cookie configuration
- LDAP TLS certificate verification made configurable
- Password piped via stdin to doveadm instead of command-line argument

## [0.2.0] - 2026-03-24

### Added
- MySQL/MariaDB backend support via Repository pattern
- Backend selection via `MAILPANEL_BACKEND` environment variable (`ldap` or `mysql`)
- Repository interfaces for auth, domain, and user operations
- `RepositoryFactory` for backend-driven dependency resolution
- MySQL repository implementations (auth, domain, user CRUD)
- `MysqlConnection` PDO singleton with proper error handling
- MySQL password hash verification (SSHA512, SSHA, CRYPT, SHA512, PLAIN-MD5, PLAIN)
- Extension validation at startup (ext-ldap or ext-pdo_mysql)
- Full user creation support for MySQL backend

### Changed
- Environment variable prefix renamed from `IREDADMIN_LIGHT_` to `MAILPANEL_`
- Controllers fully decoupled from LDAP — now use repository interfaces
- `User::$mailQuota` standardized to always store megabytes (LDAP converts bytes at repo boundary)
- Only active backend's settings are validated at startup
- `ext-ldap` moved from required to suggested in composer.json
- LDAP `createUser` now throws RuntimeException instead of silent no-op

### Fixed
- `{CRYPT}` password verification now uses `crypt()` to support MD5-crypt alongside bcrypt
- MySQL `getUsers` excludes catch-all mailbox entries (matching LDAP behavior)
- Bare MD5 hex hash detection in password verification fallback
- `userCreateView` now actually calls `createUser` and redirects on success
- `$error` variable properly passed to user creation template
