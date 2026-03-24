# Changelog

All notable changes to this project will be documented in this file.

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
